<?php

namespace ExprAs\User\MezzioAuthentication;

use Doctrine\ORM\EntityManager;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\User\Entity\User;
use Laminas\Session\Container as SessionContainer;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SessionAdapter extends AbstractAdapter
{
    /**
     * Object to proxy $_SESSION storage
     *
     * @var SessionContainer
     */
    protected $session;

    protected $storageKey = UserInterface::class;

    /**
     * @var EntityManager::class
     */
    protected $entityManager;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * @var DefaultRepository  
     */
    protected $userRepository;

    /**
     * SessionAdapter constructor.
     */
    public function __construct(DefaultRepository $userRepository, callable $responseFactory, SessionContainer $session)
    {
        $this->userRepository = $userRepository;
        $this->responseFactory = $responseFactory;
        $this->session = $session;
    }





    /**
     * Authenticate the PSR-7 request and return a valid user
     * or null if not authenticated
     */
    #[\Override]
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        /**
 * @var User|null $userObject 
*/
        if (($userObject = parent::authenticate($request))) {
            $this->session->offsetSet($this->storageKey, $userObject->getId());
        }

        $user = $this->session->offsetGet($this->storageKey);
        return $this->userRepository->find($user ?: 0);
    }

    /**
     * @param  ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        $this->session->offsetUnset($this->storageKey);
        return ($this->responseFactory)();
    }

    /**
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    #[\Override]
    public function complete(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!$this->session->offsetExists($this->storageKey) && ($userObject = $request->getAttribute(User::class))) {
            $this->session->offsetSet($this->storageKey, $userObject->getId());
        }
        return $handler->handle($request);
    }
}
