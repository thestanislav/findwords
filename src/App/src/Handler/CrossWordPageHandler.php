<?php

namespace App\Handler;

use App\Entity\Definition;
use App\Entity\Dictionary;
use App\Entity\Word;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\Handler\TemplateRendererProviderTrait;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Foolz\SphinxQL\Drivers\Mysqli\Connection;
use Foolz\SphinxQL\SphinxQL;

class CrossWordPageHandler extends AbstractActionHandler
{
    use EntityManagerAwareTrait;
    use TemplateRendererProviderTrait;

    protected $_dictionary;

    /**
     * @return Dictionary | null | object
     */
    public function getDictionary()
    {
        if (!$this->_dictionary) {
            $this->_dictionary = $this->getEntityManager()->getRepository(Dictionary::class)->findOneBy(['definedName' => 'crossword']);
        }
        return $this->_dictionary;
    }


    public function indexAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if (!($query = $this->getQueryParams()->get('query')) && !is_null($query)) {
            $urlHelper = $this->getContainer()->get(UrlHelper::class);
            return new RedirectResponse($urlHelper('dictionary-crossword', []));
        }

        $templateVariables = [];

        if (($query = $this->getQueryParams()->get('query'))) {

            $query = trim(rawurldecode($query));

            $queryNormalized = preg_replace('~\d+\s*letter[s]*~u', '', $query);

            /** @var  Connection $sphinxConnection */
            $sphinxConnection = $this->getContainer()->get(Connection::class);
            $spQlQuery = (new SphinxQL($sphinxConnection))->select('idx')
                ->from('findwords_dictionary_words_index')
                ->match('content', $queryNormalized)
                ->orderBy('weight()', 'desc')
                ->limit(0, 50);

            $dqlParams = [
                'idx' => array_map(function ($v) {
                        return $v['idx'];
                    }, iterator_to_array($spQlQuery->execute())) + [0]
            ];
            /** @var DefaultRepository $definitionRepo */
            $definitionRepo = $this->getEntityManager()->getRepository(Definition::class);
            $dql = sprintf('select e from %s e where e.id in (:idx)', Definition::class);
            if (preg_match('~(\d+)\s*letters?$~u', $query, $match)) {
                $dql .= sprintf(' and exists (select w from %s w where w = e.word and w.length = :length)', Word::class);
                $dqlParams['length'] = $match[1];
            }
            $dqlParams['idset'] = implode(',', $dqlParams['idx']);
            $dql .= ' order by FIND_IN_SET( e.id, :idset) asc';

            $definitions = $definitionRepo->findByDql($dql, $dqlParams, 20);

            if ($definitions->count() == 1 &&
                levenshtein(mb_strtolower($queryNormalized), mb_strtolower($definitions->current()->getContent())) < ceil(mb_strlen($definitions->current()->getContent()) * 0.5)) {

                $urlHelper = $this->getContainer()->get(UrlHelper::class);
                return new RedirectResponse($urlHelper('dictionary-crossword-definition', ['definition_id' => $definitions->current()->getId()]));
            }

            $templateVariables['definitions'] = $definitions;
        } else {
            $dql = sprintf(
                'select e, rand(%d) as hidden srt from %s e where length(e.content) < 64 and e.dictionary = :dictionary order by srt',
                rand(0, 900000),
                Definition::class
            );
            $dqlQuery = $this->getEntityManager()->createQuery($dql);
            $dqlQuery->setParameter('dictionary', $this->getDictionary());
            $dqlQuery->setMaxResults(5);

            $templateVariables['exampleDefinitions'] = $dqlQuery->getResult();
        }

        $templateVariables['query'] = trim($query ?? '');

        return new HtmlResponse($this->getRenderer()->render('cross-word::index', $templateVariables));
    }

    public function definitionAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        /** @var DefaultRepository $definitionRepo */
        $definitionRepo = $this->getEntityManager()->getRepository(Definition::class);

        /** @var $definition Definition */
        if (!($definition = $definitionRepo->findOneBy([
            'dictionary' => $this->getDictionary(),
            'id'         => $request->getAttribute('definition_id', 0)
        ]))) {
            return $delegate->handle($request);
        }


        $otherDefinitions = $definition->getWord()->getDefinitions()->toArray();
        shuffle($otherDefinitions);
        $otherDefinitions = array_slice($otherDefinitions, 0, 10);


        $templateVariables = [
            'definition' => $definition,
            'alternativeDefinitions' => array_filter($otherDefinitions, function ($def) use ($definition) {
                return $definition->getId() != $def->getId() && $this->getDictionary()->getId() == $def->getDictionary()->getId();
            }),
            'dictionaryDefinitions' => array_filter($otherDefinitions, function ($def) {
                return $this->getDictionary()->getId() != $def->getDictionary()->getId();
            }),

        ];

        return new HtmlResponse($this->getRenderer()->render('cross-word::definition', $templateVariables));

    }
}