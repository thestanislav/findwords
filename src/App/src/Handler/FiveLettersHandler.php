<?php

namespace App\Handler;

use App\Entity\Word;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\Handler\TemplateRendererProviderTrait;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Stdlib\Parameters;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class FiveLettersHandler extends AbstractActionHandler
{
    use EntityManagerAwareTrait;
    use TemplateRendererProviderTrait;

    public function indexAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {

        $content = $this->getRenderer()->render('five-letters::index', [
            'mask' => '*****',
            'exclude' => '',
            'known' => '',
        ]);

        return new HtmlResponse($content);
    }

    public function searchAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {

        $query = new Parameters($request->getQueryParams());
        $currentPage = $query->get('page', 1);

        $mask = preg_replace('~[^a-z]~u', '_', mb_strtolower(urldecode($request->getAttribute('mask') ?? '')));
        $known = preg_replace('~[^a-z]~ui', '', mb_strtolower($query->get('known') ?? ''));
        $exclude =  preg_replace('~[^a-z]~ui', '', str_replace(mb_str_split($known), '', mb_strtolower($query->get('exclude') ?? '')));


        $bitmask = 0;
        foreach (preg_split('~~u', $known, -1, PREG_SPLIT_NO_EMPTY) as $_l) {
            $bitmask |= (1 << ord($_l) - 97);
            $exclude = str_replace($_l, '', $exclude);
        }


        $dqlParams = array(
            //':mask'    => $mask,
            ':bitmask' => $bitmask,
        );
        $wordRepo = $this->getEntityManager()->getRepository(Word::class);

        $dql = sprintf('select e from %s e', $wordRepo->getClassName());
        $dql .= ' where e.isPhrase = false and BIT_AND(:bitmask, e.letterMask) = :bitmask';


        foreach (
            preg_split('~~u', $exclude, -1, PREG_SPLIT_NO_EMPTY) as $_k =>
            $_l
        ) {

            $excludeBit = (1 << ord($_l) - 97);
            $dql .= sprintf(' and BIT_AND(:excludeBit%d, e.letterMask) != :excludeBit%d', $_k, $_k);
            $dqlParams['excludeBit' . $_k] = $excludeBit;
        }

        $dql .= ' and e.length=:length';
        $dqlParams['length'] = mb_strlen($mask);

        $dql .= ' order by e.length asc, e.word asc';

        $itemCountPerPage = 900;
        $offset = ($currentPage - 1) * $itemCountPerPage;

        $dqlQuery = $this->getEntityManager()->createQuery($dql);
        $dqlQuery->setParameters($dqlParams)
            ->setFirstResult($offset)
            ->setMaxResults($itemCountPerPage);

        $terms = new \ArrayIterator($dqlQuery->getResult());
        $paginator = [
            'hasPreviousPage' => ($currentPage > 1),
            'currentPage'     => $currentPage,
            'hasNextPage'     => (count($terms) == $itemCountPerPage),
            'queryParams' => [
                'known' => $query->get('known', ''),
                'exclude' => $query->get('exclude', ''),
            ]
        ];

        $content = $this->getRenderer()->render('five-letters::index', [
            'terms'  => $terms,
            'paginator' => $paginator,
            'mask' => str_replace('_', '*', $mask),
            'exclude' => $exclude,
            'known' => $known,
        ]);

        return new HtmlResponse($content);
    }
}