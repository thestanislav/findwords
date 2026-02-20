<?php

namespace ExprAs\User\MezzioAuthentication;

use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use ExprAs\User\Entity\RememberMeSuper;
use ExprAs\User\Service\RememberMeService;
use Laminas\Stdlib\Parameters;
use Mezzio\Authentication\UserInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RememberMeAdapter extends AbstractAdapter
{
    use ServiceContainerAwareTrait;

    protected $_cookieSet = false;

    protected $_cookieUnset = false;

    /**
     * @var callable
     */
    protected $responseFactory;

    /**
     * @var RememberMeSuper
     */
    protected $_entity;

    /**
     * RememberMeAdapter constructor.
     *
     * @param $service
     */
    public function __construct(callable $responseFactory, protected $service)
    {
        $this->responseFactory = $responseFactory;
    }


    /**
     * @return RememberMeService
     */
    public function getService()
    {
        return $this->service;
    }


    #[\Override]
    public function authenticate(ServerRequestInterface $request): ?UserInterface
    {
        if (($user = parent::authenticate($request))) {
            if ((new Parameters($request->getParsedBody()))->get('remember_me') == 1) {
                $this->_entity = $this->getService()->createSerie($user);
                $this->_cookieSet = true;
            }

            return $user;
        }


        // no cookie present, skip authentication
        if (!($cookie = $this->getService()::getCookie($request))) {
            return null;
        }

        if ((is_countable($cookie) ? count($cookie) : 0) !== 3) {
            $this->_cookieUnset = true;
            return null;
        }

        /**
 * @var RememberMeSuper $rememberMe 
*/
        $rememberMe = $this->getService()->getRepository()
            ->findOneBy(['user' => $cookie[0], 'sid' => $cookie[1]]);

        if (!$rememberMe) {
            $this->_cookieUnset = true;
            return null;
        }

        if ($rememberMe->getToken() !== $cookie[2]) {
            // H4x0r
            // @TODO: Inform user of theft, change password?
            $entries = $this->getService()->getRepository()->findBy(['user' => $cookie[0]]);
            foreach ($entries as $_entry) {
                $this->getService()->getEntityManager()->remove($_entry);
            }
            $this->getService()->getEntityManager()->flush();
            $this->_cookieUnset = true;
            return null;
        }

        if (!($userObject = $rememberMe->getUser())) {
            $this->_cookieUnset = true;
            return null;
        };

        //$this->getService()->updateSerie($rememberMe);
        $this->_cookieSet = true;

        return $userObject;
    }

    /**
     * @param  ResponseInterface $response
     * @return ResponseInterface
     */
    #[\Override]
    public function complete(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($this->_cookieSet && $this->_entity instanceof RememberMeSuper) {
            return $this->getService()->setCookie($response, $this->_entity);
        } elseif ($this->_cookieUnset) {
            return $this->getService()->removeCookie($response);
        }

        return $response;
    }

    public function unauthorizedResponse(ServerRequestInterface $request): ResponseInterface
    {
        // no cookie present, skip authentication
        if (($cookie = $this->getService()::getCookie($request)) && (is_countable($cookie) ? count($cookie) : 0) === 3) {

            $entries = $this->getService()->getRepository()->findBy(['user' => $cookie[0], 'sid' => $cookie[1]]);
            foreach ($entries as $_entry) {
                $this->getService()->getEntityManager()->remove($_entry);
            }
            $this->getService()->getEntityManager()->flush();
        }


        return $this->getService()->removeCookie(($this->responseFactory)());
    }

}
