<?php

namespace App\Handler;

use App\Entity\Word;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\Handler\TemplateRendererProviderTrait;
use ExprAs\Core\Stdlib\StringUtils;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AnagramPageHandler extends AbstractActionHandler
{
    use EntityManagerAwareTrait;
    use TemplateRendererProviderTrait;

    public function indexAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {

        /** @var Word $wordEntity */
        /** @var DefaultRepository $wordRepo */
        /** @var DefaultRepository $repo */

        $templateVariables = [];
        $repo = $this->getEntityManager()->getRepository('App\Entity\Word');

        if ($letters = urldecode($request->getAttribute('term') ?? '')) {

            if (!preg_match('~^[a-z]+$~iu', $letters)) {

                return new HtmlResponse($this->getRenderer()->render('anagram::error', [
                    'letters' => $letters
                ]));

            } else {
                $bitmask = 0;
                $letters = mb_strtolower($letters);
                foreach (str_split(preg_replace('~\W~', '', preg_replace('~[^a-z]+~i', '', mb_strtolower($letters)))) as $_l) {
                    if (!strlen($_l)) {
                        break;
                    }
                    $bitmask |= (1 << ord($_l) - 97);
                }

                $dqlParams = array(
                    ':word' => str_replace(' ', '', $letters),
                    ':bitmask' => ~$bitmask,
                );
                $dql = 'select e from App\Entity\Word e where e.word != :word and BIT_AND(e.letterMask, :bitmask) = 0
                                and REGEXP (e.word, \'^[a-z\-]+$\') = 1
                                and e.letterMask > 0 and e.length > 2 and e.isPhrase = false';

                if ($length = $request->getAttribute('length')) {
                    $dql .= ' and e.length = :length';
                    $dqlParams[':length'] = $length;
                }
                $dql .= ' order by e.length asc, e.word asc';

                $pageNumber = intval($this->getQueryParams()->get('page', 1));
                $itemCountPerPage = 1000;
                $offset = ($pageNumber - 1) * $itemCountPerPage;
                $query = $this->getEntityManager()->createQuery($dql);
                $query->setParameters($dqlParams)
                    ->setMaxResults($itemCountPerPage)
                    ->setFirstResult($offset);
                $composesWords = $query->getResult();
                $paginator = [
                    'hasPreviousPage' =>  ($pageNumber > 1),
                    'currentPage' => $pageNumber,
                    'hasNextPage' => (count($composesWords) == $itemCountPerPage)
                ];

                $dqlParams = array(
                    ':word'   => $letters,
                    ':mg'     => StringUtils::generateAnagramKey($letters),
                    ':length' => mb_strlen($letters),
                    ':bitmask' => ~ $bitmask,
                );
                $dql = 'select e from App\Entity\Word e where e.word != :word
                    and BIT_AND(e.letterMask, :bitmask) = 0 
                    and e.anagramKey = :mg and e.length = :length 
                    and e.isPhrase = false';
                $dql .= ' order by e.word asc';

                $anagram = $repo->findAllByDql($dql, $dqlParams);

                $templateVariables['composesWords'] = $composesWords;
                $templateVariables['anagram'] = $anagram;
                $templateVariables['letters'] = $letters;
                $templateVariables['paginator'] = $paginator;
                $templateVariables['length'] = $length;
                $templateVariables['word'] = $repo->findOneBy(['word' => $letters]);
            }
        }

        return new HtmlResponse($this->getRenderer()->render('anagram::index', $templateVariables));
    }

    public function searchAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if ($query = $this->getQueryParams()->get('query')) {
            $query = mb_strtolower(trim($query));
            /** @var DefaultRepository $repo */
            $repo = $this->getEntityManager()->getRepository(Word::class);
            if ($term = $repo->findOneBy(array('word' => $query))) {
                $queryParam = $request->getQueryParams();
                unset($queryParam['query']);
                $urlHelper = $this->getContainer()->get(UrlHelper::class);

                return new RedirectResponse($urlHelper('dictionary-anagram-term', [ 'term' => rawurlencode($term->getWord())], $queryParam));
            }

            list($query) = preg_split('~\s+~', $query);
        }
        return $this->process($request->withAttribute('action', 'index')->withAttribute('term', $query), $delegate);
    }
}