<?php

namespace App\Handler;

use App\Entity\Definition;
use App\Entity\Dictionary;
use App\Entity\Phrase;
use App\Entity\Word;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\Handler\TemplateRendererProviderTrait;
use ExprAs\Doctrine\Repository\DefaultRepository;
use ExprAs\Doctrine\Service\EntityManagerAwareTrait;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use phpMorphy;
use Mezzio\Helper\UrlHelper;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class WordPageHandler extends AbstractActionHandler
{
    use TemplateRendererProviderTrait;
    use EntityManagerAwareTrait;

    /** @var DefaultRepository */
    protected $_wordRepository;

    protected $_phpMorphy;

    /**
     * @return phpMorphy
     */
    public function getPhpMorphy()
    {
        if (!$this->_phpMorphy) {
            $this->_phpMorphy = new phpMorphy(
                realpath('data/dicts'), 'en_EN', array(
                    'storage' => PHPMORPHY_STORAGE_MEM,
                )
            );
        }
        return $this->_phpMorphy;
    }

    public function indexAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        /** @var Word $wordEntity */

        $wordRepo = $this->getWordRepository();
        $em = $this->getEntityManager();

        if (($_word = urldecode($request->getAttribute('term') ?? ''))
            && !($wordEntity = $wordRepo->findOneBy(array('word' => $_word)))
        ) {
            return $delegate->handle($request);
        }

        $data = [
            'query' => $this->getQueryParams()->get('query', $_word)
        ];
        if (isset($wordEntity) && $wordEntity instanceof Word) {

            $rsm = new ResultSetMappingBuilder($em);
            $rsm->addRootEntityFromClassMetadata(Definition::class, 'e');
            //query with union is much faster than or
            $sql = sprintf(
                'select e.* from
                              (select * from %s where word_id =?
                                  union
                               select * from %s where word_id in (select phrase_word_id from %s where related_word_id =?)
                               ) e',
                $em->getClassMetadata(Definition::class)->getTableName(),
                $em->getClassMetadata(Definition::class)->getTableName(),
                $em->getClassMetadata(Phrase::class)->getTableName()
            );
            $query = $em->createNativeQuery($sql, $rsm);
            $query->setParameter(1, $wordEntity->getId());
            $query->setParameter(2, $wordEntity->getId());

            $data['word'] = $wordEntity;
            $data['definitions'] = new \ArrayIterator($query->getResult());
        }


        return new HtmlResponse(
            $this->getRenderer()->render('word::index', $data)
        );
    }

    /**
     * @return DefaultRepository
     */
    public function getWordRepository()
    {
        if (!$this->_wordRepository) {
            $this->_wordRepository = $this->getEntityManager()->getRepository(Word::class);
        }
        return $this->_wordRepository;
    }

    public function termAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $urlHelper = $this->getContainer()->get(UrlHelper::class);
        return new RedirectResponse($urlHelper('dictionary-term', $request->getAttributes()), 301);

    }

    public function searchAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $urlHelper = $this->getContainer()->get(UrlHelper::class);

        $wordRepo = $this->getWordRepository();
        $_query = trim($this->getQueryParams()->get('query') ?? '');
        if ($_query && ($wordEntity = $wordRepo->findOneBy(array('word' => mb_strtolower($_query))))) {
            return new RedirectResponse($urlHelper('dictionary-term', array('term' => rawurlencode($wordEntity->getWord()))));
        }

        $_queryUpper = mb_strtoupper($_query);
        $baseForm = $this->getPhpMorphy()->getBaseForm($_queryUpper, phpMorphy::IGNORE_PREDICT);
        if ($baseForm) {
            $baseForm = mb_strtolower(current($baseForm));
            if (($wordEntity = $wordRepo->findOneBy(array('word' => $baseForm)))) {
                return new RedirectResponse($urlHelper('dictionary-term', array('term' => rawurlencode($wordEntity->getWord()))));
            }
        }

        return $this->process($request->withAttribute('action' , 'index'), $delegate);
    }

    public function endsAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $data = ['action' => 'ends'];

        if ($chars = $request->getAttribute('chars', $this->getQueryParams()->get('query'))) {
            $chars = mb_strtolower(urldecode($chars));
            $data += $this->termsByMask('%' . $chars, intval($request->getAttribute('length')));
            $data['chars']  =  urldecode($request->getAttribute('chars') ?? '');
            $asteriskCount = max(0, $request->getAttribute('length', 2 + mb_strlen($chars)) - mb_strlen($chars));
            $mask = str_repeat('*', $asteriskCount);
            $mask .= $chars;
            $data['mask'] = $mask;
        }
        return new HtmlResponse($this->getRenderer()->render('word::ends', $data));
    }

    public function startsAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $data = ['action' => 'starts'];

        if ($chars = $request->getAttribute('chars', $this->getQueryParams()->get('query'))) {
            $chars = mb_strtolower(urldecode($chars));
            $data += $this->termsByMask($chars . '%', intval($request->getAttribute('length')));
            $data['chars']  =  urldecode($request->getAttribute('chars') ?? '');
            $asteriskCount = max(0, $request->getAttribute('length', 2 + mb_strlen($chars)) - mb_strlen($chars));
            $mask = $chars;
            $mask .= str_repeat('*', $asteriskCount);
            $data['mask'] = $mask;
        }
        return new HtmlResponse($this->getRenderer()->render('word::starts', $data));
    }

    public function containsAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {

        $data = ['action' => 'contains'];
        if ($chars = $request->getAttribute('chars', $this->getQueryParams()->get('query'))) {
            $chars = mb_strtolower(urldecode($chars));
            $data += $this->termsByMask('%' . $chars . '%', intval($request->getAttribute('length')));
            $data['chars']  =  urldecode($request->getAttribute('chars') ?? '');

            $asteriskCount = max(0, $request->getAttribute('length', 2 + mb_strlen($chars)) - mb_strlen($chars));
            $mask = str_repeat('*', $asteriskCount / 2);
            $mask .= $chars;
            $mask .= str_repeat('*', $asteriskCount / 2 + $asteriskCount % 2);
            $data['mask'] = $mask;
        }

        return new HtmlResponse($this->getRenderer()->render('word::contains', $data));
    }

    public function maskAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        $data = ['action' => 'mask'];

        if (($chars = urldecode($request->getAttribute('chars', mb_strtolower($this->getQueryParams()->get('query') ?? ''))))
            && (preg_match_all('~[\p{Ll}\p{Lm}\p{Lo}]+~u', $chars, $m))
        ) {
            $foundChars = array();
            if ($m[0]) {
                $foundChars = $m[0];
            }

            $queryParams = array_filter($request->getQueryParams(), function ($v, $k) {
                return $k == 'filter';
            }, ARRAY_FILTER_USE_BOTH);

            if (count($foundChars) == 1 && mb_strlen($foundChars[0]) < 4) {

                $urlHelper = $this->getContainer()->get(UrlHelper::class);
                if (preg_match('~^[\p{Ll}\p{Lm}\p{Lo}]+~u', $chars)) {
                    return new RedirectResponse($urlHelper('dictionary-contains', [
                        'chars' => rawurlencode($foundChars[0]),
                        'action' => 'starts'
                    ], $queryParams));

                } elseif (preg_match('~[\p{Ll}\p{Lm}\p{Lo}]+$~u', $chars)) {
                    return new RedirectResponse($urlHelper('dictionary-contains', [
                        'chars' => rawurlencode($foundChars[0]),
                        'action' => 'ends'
                    ], $queryParams));
                } else {
                    return new RedirectResponse($urlHelper('dictionary-contains', [
                        'chars' => rawurlencode($foundChars[0]),
                        'action' => 'contains'
                    ], $queryParams));
                }
            }

            $mask = preg_replace('~[^\p{Ll}\p{Lm}\p{Lo}]~u', '_', $chars);
            $pattern = $chars;
            if (!$request->getAttribute('length')) {
                $mask = rtrim($mask, '_') . '%';
                $pattern = rtrim($pattern, '*') . '*';
            }
            $data += $this->termsByMask($mask, $request->getAttribute('length'));

            $data['mask'] = $pattern;
            $data['chars'] = $foundChars;


        }

        return new HtmlResponse($this->getRenderer()->render('word::mask', $data));
    }

    /**
     * @param      $mask
     * @param null $length
     *
     * @return array
     */
    public function termsByMask($mask, $length = null)
    {

        $bitmask = 0;
        foreach (preg_split('~~u', preg_replace('~[^а-я]~ui', '', $mask), -1, PREG_SPLIT_NO_EMPTY) as $_l) {
            $bitmask |= (1 << ord($_l) - 97);
        }
        $dqlParams = array(
            ':mask'    => $mask,
            ':bitmask' => $bitmask
        );
        $wordRepo = $this->getWordRepository();
        $dql = sprintf('select e from %s e', $wordRepo->getClassName());
        $dql .= ' where e.isPhrase = false and BIT_AND(:bitmask, e.letterMask) = :bitmask and e.word like :mask';
        if ($this->getQueryParams()->get('filter') == 'crossword') {
            $dql .= sprintf(' and exists (select d from %s d where d.word = e and d.dictionary = :dictionary)', Definition::class);
            $dqlParams['dictionary'] = $this->getEntityManager()->getRepository(Dictionary::class)->findOneBy(['definedName' => 'crossword']) ?: 0;
        }
        if ($length) {
            $dql .= ' and e.length=:length';
            $dqlParams[':length'] = $length;
        }
        $dql .= ' order by e.length asc, e.word asc';

        $itemCountPerPage = 900;
        $currentPage = intval($this->getQueryParams()->get('page', 1));
        $offset = ($currentPage - 1) * $itemCountPerPage;
        $query = $this->getEntityManager()->createQuery($dql);
        $query->setParameters($dqlParams)
            ->setMaxResults($itemCountPerPage)
            ->setFirstResult($offset);

        $terms = new \ArrayIterator($query->getResult());
        $paginator = [
            'hasPreviousPage' => ($currentPage > 1),
            'currentPage'     => $currentPage,
            'hasNextPage'     => (count($terms) == $itemCountPerPage)
        ];

        return  [
            'terms'  => $terms,
            'length' => $length,
            'paginator' => $paginator,
            'filter' => $this->getQueryParams()->get('filter')
        ];
    }

}