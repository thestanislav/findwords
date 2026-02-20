<?php

namespace ExprAs\User\Service;

use ExprAs\User\Entity\RememberMeSuper;
use Laminas\Http\Header\SetCookie;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use ExprAs\User\Entity\UserSuper;
use Laminas\Diactoros\ServerRequest;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ResponseInterface;

class RememberMeService
{
    use EntityManagerAwareTrait;

    /**
     * @var Parameters
     */
    protected $_parameters;

    protected $_repository;

    /**
     * @return DefaultRepository
     */
    public function getRepository()
    {
        if (!$this->_repository) {
            $this->_repository = $this->getEntityManager()->getRepository($this->getParameters()->get('entity_name'));
        }
        return $this->_repository;
    }


    /**
     * @return Parameters
     */
    public function getParameters()
    {
        return $this->_parameters;
    }

    public function setParameters(Parameters $parameters): void
    {
        $this->_parameters = $parameters;
    }


    public function createToken($length = 16)
    {
        // Generate enough bytes so base64 encoding gives us at least $length characters
        $bytes = ceil($length * 3 / 4); // Base64 is ~33% larger than binary
        $encoded = base64_encode(random_bytes($bytes));
        return substr($encoded, 0, $length);
    }

    public function createSerieId($length = 16)
    {
        // Generate enough bytes so base64 encoding gives us at least $length characters
        $bytes = ceil($length * 3 / 4); // Base64 is ~33% larger than binary
        $encoded = base64_encode(random_bytes($bytes));
        return substr($encoded, 0, $length);
    }

    /**
     * @param $entity \ExprAs\User\Entity\RememberMe
     *
     * @return void
     */
    public function updateSerie($entity)
    {
        /**
 * @var \ExprAs\User\Entity\RememberMe $rememberMe 
*/
        $rememberMe = $this->getRepository()
            ->findOneBy(['user' => $entity->getUser(), 'sid' => $entity->getSid()]);

        if ($rememberMe) {
            // Update serie with new token
            $token = $this->createToken();
            $rememberMe->setToken($token);
            $this->getEntityManager()->persist($rememberMe);
            $this->getEntityManager()->flush($rememberMe);
        }
    }

    public function createSerie(UserSuper $user)
    {
        $token = $this->createToken();
        $serieId = $this->createSerieId();

        /**
 * @var \ExprAs\User\Entity\RememberMe $rememberMe 
*/
        $entityName = $this->getParameters()->get('entity_name');
        ;
        $rememberMe = new $entityName();
        $rememberMe->setUser($user);
        $rememberMe->setSid($serieId);
        $rememberMe->setToken($token);
        $this->getEntityManager()->persist($rememberMe);
        $this->getEntityManager()->flush($rememberMe);
        return $rememberMe;
    }

    public function removeSerie($userId, $serieId)
    {
        $serie = $this->getRepository()->findOneBy(['userId' => $userId, 'sid' => $serieId]);
        $this->getEntityManager()->remove($serie);
        $this->getEntityManager()->flush($serie);
    }

    public function removeCookie(ResponseInterface $response)
    {
        $cookieName = $this->getParameters()->get('cookie_name');
        $cookieDomain = $this->getParameters()->get('cookie_domain');
        $setCookie = new SetCookie(
            $cookieName,
            null,
            time() - 3600,
            '/',
            $cookieDomain
        );

        return $response->withAddedHeader('Set-Cookie', $setCookie->getFieldValue());
    }

    public static function getCookie(ServerRequest $request)
    {
        $cookie = urldecode((string) (new Parameters($request->getCookieParams()))->get('remember_me', ''));
        if (preg_match('~^(\d+)\|(.{16})\|(.{16})$~', $cookie, $match)) {
            return array_slice($match, 1);
        }
        return null;
    }

    public function setCookie(ResponseInterface $response, RememberMeSuper $entity)
    {
        $cookieLength = $this->getParameters()->get('cookie_expire');
        $cookieDomain = $this->getParameters()->get('cookie_domain');
        $cookieName = $this->getParameters()->get('cookie_name');
        $cookieValue = $entity->getUser()->getId() . "|" . $entity->getSid() . "|" . $entity->getToken();
        $setCookie = new SetCookie(
            $cookieName,
            urlencode($cookieValue),
            time() + intval($cookieLength),
            '/',
            $cookieDomain
        );
        return $response->withAddedHeader('Set-Cookie', $setCookie->getFieldValue());

    }

}
