<?php

namespace App\Handler;

use App\Entity\Word;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\Handler\TemplateRendererProviderTrait;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RhymePageHandler extends AbstractActionHandler
{
    use EntityManagerAwareTrait;
    use TemplateRendererProviderTrait;

    public function indexAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        /** @var Word $wordEntity */
        /** @var DefaultRepository $wordRepo */
        /** @var DefaultRepository $repo */

        $templateVariables = [];

        if ($term = urldecode($request->getAttribute('term') ?? '')) {

            $templateVariables['termQuery'] = $term;

            $word = preg_replace('~[^a-zA-Z]~', '', $term);
            exec(sprintf('/usr/bin/espeak-ng -xq "%s"', $word), $phoneme);

            if (!count($phoneme)) {
                return $delegate->handle($request);
            }
            if (!strlen($word)) {
                return new HtmlResponse($this->getRenderer()->render('rhyme::index', array_merge($templateVariables, [
                    'strongRhymes' => new \ArrayIterator([]),
                    'rhymes' => new \ArrayIterator([]),
                    'word' => $word,
                ])));
            }

            $phoneme = $phoneme[0];

            $dqlParams = array(
                ':word' => $word,
            );

            $currentPageNumber = intval($this->getQueryParams()->get('page', 1));
            $itemCountPerPage = 500;
            $offset = ($currentPageNumber - 1 - ($currentPageNumber == 1?0:1)) * $itemCountPerPage;

            $i = $charCount = $strongCharCount = 0;
            while ($i < 2 && strlen($phoneme) > $charCount) {
                if (preg_match('~[a-z]~i', substr($phoneme, -1 * ($charCount + 1), 1))) {
                    $i++;
                }
                $charCount++;
                $strongCharCount++;
            }

            while ($i < 3 && strlen($phoneme) > $strongCharCount) {
                if (preg_match('~[a-z]~i', substr($phoneme, -1 * ($strongCharCount + 1), 1))) {
                    $i++;
                }
                $strongCharCount++;
            }

            $ids = [];

            if ($charCount <= 4) {
                /** @var  Connection $sphinxConnection */
                $sphinxConnection = $this->getContainer()->get(Connection::class);
                if ($currentPageNumber == 1) {
                    $spQlQuery = (new SphinxQL($sphinxConnection))->query(
                        sprintf("SELECT idx FROM findwords_words_last_letters_index WHERE MATCH('(@last_%d_letters \"%s\")') LIMIT 0, 500 option max_matches=100000", $charCount,
                            mb_substr($word, -1 * $charCount))
                    );
                }else {
                    $spQlQuery = (new SphinxQL($sphinxConnection))->query(
                        sprintf("SELECT idx FROM findwords_words_last_letters_index WHERE MATCH('(@phoneme_last_%d_symbols \"%s\")') LIMIT %d, %d option max_matches=100000",
                            $charCount, mb_substr($word, -1 * $charCount), $offset, $itemCountPerPage)
                    );
                }

                $result = $spQlQuery->execute();


                iterator_apply($result, function(\Iterator $it) use (&$ids){
                    $ids[] = $it->current()['idx'];
                    return true;
                }, [$result]);
            }


            if ($strongCharCount == 2) {
                $dql = 'select e from App\Entity\Word e';
            } elseif ($strongCharCount == 3) {
                $dql = 'select e from App\Entity\Word e';
            } else {
                $dql = 'select e from App\Entity\Word e';
            }

            $dql .= ' where e.length >= :charCount and e.word != :word and e.isPhrase = false';
            if (count($ids)) {
                $dql .= sprintf(' and e.id in (%s)', implode(',', $ids));
            }

            if ($charCount == 2) {
                if ($currentPageNumber == 1) {
                    $dql .= ' and e.lastTwoLetters =:letters';
                } else {
                    $dql .= ' and e.phonemeLastTwoSymbols = :chars';
                }
            } elseif ($charCount == 3) {
                if ($currentPageNumber == 1) {
                    $dql .= ' and e.lastTreeLetters =:letters';
                } else {
                    $dql .= ' and e.phonemeLastTreeSymbols = :chars';
                }
            } elseif ($charCount == 4) {
                if ($currentPageNumber == 1) {
                    $dql .= ' and e.lastFourLetters =:letters';
                } else {
                    $dql .= ' and e.phonemeLastFourSymbols = :chars';
                }
            } else {
                if ($currentPageNumber == 1) {
                    $dql .= sprintf(' and SUBSTRING(e.word, -%d) =:letters', $charCount);
                } else {
                    $dql .= sprintf(' and SUBSTRING(e.phoneme, -%d) = :chars', $charCount);
                }
            }

            $dql .= ' order by e.word asc';


            if ($currentPageNumber == 1) {
                $dqlParams[':letters'] = mb_substr($word, -1 * $charCount);
            } else {
                $dqlParams[':chars'] = mb_substr($phoneme, -1 * $charCount);
            }


            $dqlParams[':charCount'] = $charCount;

            $query = $this->getEntityManager()->createQuery($dql);
            $query->setParameters($dqlParams);


            $query->setMaxResults($itemCountPerPage)
                ->setFirstResult($offset);


            $strongRhymes = $rhymes = array();
            if ($currentPageNumber == 1) {
                $strongRhymes = $query->getResult();
            } else {
                $rhymes = $query->getResult();
            }


            $paginator = [
                'hasPreviousPage' => ($currentPageNumber > 1),
                'currentPage' => $currentPageNumber,
                'hasNextPage' => ($currentPageNumber == 1 || (count($strongRhymes) + count($rhymes)) == $itemCountPerPage)
            ];

            $templateVariables +=  [
                'strongRhymes' => new \ArrayIterator($strongRhymes),
                'rhymes' => new \ArrayIterator($rhymes),
                'word' => $word,
                'paginator' => $paginator
            ];
        }

        return new HtmlResponse($this->getRenderer()->render('rhyme::index', $templateVariables));
    }

    public function searchAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if ($query = $this->getQueryParams()->get('query')) {
            $query = mb_strtolower(trim($query));
            /** @var DefaultRepository $repo */
            $repo = $this->getEntityManager()->getRepository('App\Entity\Word');
            if ($term = $repo->findOneBy(array('word' => $query))) {
                $queryParam = $request->getQueryParams();
                unset($queryParam['query']);
                $urlHelper = $this->getContainer()->get(UrlHelper::class);

                return new RedirectResponse($urlHelper('dictionary-rhyme-term', [ 'term' => rawurlencode($term->getWord())], $queryParam));
            }
            list($query) = preg_split('~\s+~', $query);
        }
        return $this->process($request->withAttribute('action', 'index')->withAttribute('term', $query), $delegate);
    }
}