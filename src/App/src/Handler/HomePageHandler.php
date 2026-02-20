<?php

declare(strict_types=1);

namespace App\Handler;


use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\Handler\TemplateRendererProviderTrait;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class HomePageHandler extends AbstractActionHandler
{
    use TemplateRendererProviderTrait;
    use EntityManagerAwareTrait;

    public function indexAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $em = $this->getEntityManager();
        /** @var DefaultRepository $wordsRepo */
        $wordsRepo = $em->getRepository('App\Entity\Word');
        $seed = (int) (new \IntlDateFormatter(null, \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'yyyyMM'))->format(new \DateTimeImmutable());
        $dql = sprintf('select e, rand(%d) as hidden r from App\Entity\Word e
            where e.isPhrase = false and e.length <7 and e.length > 3 order by r', $seed);

        return new HtmlResponse($this->getRenderer()->render('app::home-page', [
            'randomWords' => $wordsRepo->findByDql($dql, array(), 50)
        ]));
    }
}
