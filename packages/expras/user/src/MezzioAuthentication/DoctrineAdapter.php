<?php

namespace ExprAs\User\MezzioAuthentication;

use Doctrine\ORM\EntityManager;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\User\Entity\User;
use ExprAs\User\Entity\UserSuper;
use Laminas\Stdlib\Parameters;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class DoctrineAdapter extends AbstractAdapter
{
    /**
     * @var callable
     */
    protected $credentialPreprocessor;


    protected $userRepository;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * DoctrineAdapter constructor.
     */
    public function __construct(DefaultRepository $userRepository, callable $responseFactory, ?callable $credentialPreprocessor = null)
    {
        $this->userRepository = $userRepository;
        $this->credentialPreprocessor = $credentialPreprocessor;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return DefaultRepository
     */
    public function getUserRepository(): DefaultRepository
    {
        return $this->userRepository;
    }


    /**
     * @param ServerRequestInterface $request
     *
     * @return UserInterface|null
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    #[\Override]
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        /**
 * @var User|null $userObject 
*/
        if (($userObject = parent::authenticate($request))) {
            return $userObject;
        }


        $parsedBody = new Parameters($request->getParsedBody());
        if (!($identity = $parsedBody->get('identity')) || !($credential = $parsedBody->get('credential'))) {
            return null;
        }

        $credential = $this->preProcessCredential($credential);

        $userObject = $this->getUserRepository()->findByDql(
            sprintf(
                'select e from %s e where e.email = :identity or e.username = :identity',
                $this->getUserRepository()
                    ->getClassName()
            ),
            ['identity' => $identity]
        )->current();

        if (!$userObject) {
            return null;
        }

        // Don't allow user to login if state is not in allowed list
        if (!$userObject->isActive()) {
            return null;
        }

        if (!password_verify((string) $credential, (string) $userObject->getPassword())) {
            return null;
        }

        // Update user's password hash if the cost parameter has changed
        $this->updateUserPasswordHash($userObject, $credential);

        // Update last login timestamp
        $userObject->setLastLoginAt(new \DateTimeImmutable());
        $this->getUserRepository()->saveEntity($userObject);

        return $userObject;
    }

    protected function updateUserPasswordHash(UserSuper $userObject, $password)
    {
        $hash = explode('$', $userObject->getPassword());
        $currentCost = $hash[2] ?? null;
        $defaultCost = PASSWORD_BCRYPT_DEFAULT_COST;

        if ($currentCost === (string)$defaultCost) {
            return;
        }
        $userObject->setPassword(password_hash((string) $password, PASSWORD_BCRYPT));
        $this->getUserRepository()->saveEntity($userObject);
        return $this;
    }

    public function preProcessCredential($credential)
    {
        $processor = $this->getCredentialPreprocessor();
        if (is_callable($processor)) {
            return $processor($credential);
        }

        return $credential;
    }


    /**
     * Get credentialPreprocessor.
     *
     * @return callable
     */
    public function getCredentialPreprocessor()
    {
        return $this->credentialPreprocessor;
    }

    /**
     * Set credentialPreprocessor.
     *
     * @param callable $credentialPreprocessor
     *
     * @return $this
     */
    public function setCredentialPreprocessor($credentialPreprocessor)
    {
        $this->credentialPreprocessor = $credentialPreprocessor;
        return $this;
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        return ($this->responseFactory)();
    }

}
