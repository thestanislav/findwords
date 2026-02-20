<?php

namespace ExprAs\Rest\Handler;

use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\Mapping\MappingException;
use ExprAs\Rest\Helper\ExcelExportHelper;
use ExprAs\Rest\Helper\GetListFilterHelper;
use ExprAs\Rest\Hydrator\RestHydrator;
use ExprAs\Core\Response\ExcelResponse;
use ExprAs\Doctrine\Hydrator\DoctrineEntity;
use Laminas\Hydrator\Filter\FilterProviderInterface;
use Laminas\Hydrator\HydratorPluginManager;
use Laminas\Paginator\Paginator;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use DoctrineORMModule\Paginator\Adapter\DoctrinePaginator as DoctrineModulePaginator;
use ExprAs\Core\Handler\AbstractActionHandler;
use ExprAs\Core\ServiceManager\ServiceContainerAwareTrait;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Stdlib\Parameters;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Doctrine\ORM\Query\Expr;
use Mezzio\Helper\UrlHelper;


class RestApiHandler extends AbstractActionHandler implements MiddlewareInterface
{
    use ServiceContainerAwareTrait;

    /**
     * @var EntityManager $entityManager ; 
     */
    protected $entityManager;

    protected $entityName;

    protected $classMetaData;

    /**
     * @var RestHydrator
     */
    protected $hydrator;


    /**
     * @return EntityManager
     */
    public function getEntityManager()
    {
        if (!$this->entityManager) {
            $this->entityManager = $this->getContainer()->get(EntityManager::class);
        }

        return $this->entityManager;
    }

    /**
     * @return \Doctrine\ORM\Mapping\ClassMetadata
     */
    public function getClassMetaData()
    {
        if (!$this->classMetaData) {
            $this->classMetaData = $this->getEntityManager()->getClassMetadata($this->getEntityName());
        }

        return $this->classMetaData;
    }

    /**
     * @return RestHydrator
     */
    public function getHydrator()
    {
        if (!$this->hydrator) {

            /**
             * @var HydratorPluginManager $hydratorManager 
             */
            $hydratorManager = $this->getContainer()->get(HydratorPluginManager::class);

            $this->hydrator = $hydratorManager->get(RestHydrator::class);
        }

        return $this->hydrator;
    }

    /**
     * @param string $default
     *
     * @return string
     */
    public function getEntityName(): ?string
    {
        return $this->entityName;
    }

    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
    }

    public function setHydrator(RestHydrator $hydrator): void
    {
        $this->hydrator = $hydrator;
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        return parent::process($request, $handler);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws MappingException
     * @throws \ReflectionException
     * @throws ContainerExceptionInterface
     * @throws \JsonException
     */
    public function getListAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if ($request->getMethod() != 'GET') {
            return new JsonResponse([]);
        }
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->select('e')->from($request->getAttribute('entity', $this->getEntityName()), 'e');

        $queryParams = new Parameters($request->getQueryParams());

        $where = $queryParams->get('_filter', []);

        $whereHelper = new GetListFilterHelper($this->getEntityManager(), $qb, $this->getEntityName());
        $whereHelper->addWhere($where);

        if (($_sort = $queryParams->get('_sort'))) {
            $this->addOrderBy($qb, $_sort, strtolower((string) $queryParams->get('_order', '')) == 'desc' ? 'desc' : 'asc');
        }

        $dqlQuery = $qb->getQuery()->setFirstResult($_start = intval($queryParams->get('_start', 0)));
        if ($queryParams->get('_end')) {
            $dqlQuery->setMaxResults(intval($queryParams->get('_end')) - $_start);
        }

        $paginator = new Paginator(new DoctrineModulePaginator(new DoctrinePaginator($dqlQuery, $fetchJoinCollection = true)));


        if ($request->hasHeader('Accept') && $request->getHeader('Accept')[0] == 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') {

            $fields = json_decode(urldecode($request->getHeader('X-Export-Fields')[0]), true, 512, JSON_THROW_ON_ERROR);

            // Parse field configurations - normalize simple and extended formats
            $fieldConfigs = [];
            foreach ($fields as $field => $config) {
                if (is_string($config)) {
                    // Simple format - backward compatible
                    $fieldConfigs[$field] = ['label' => $config];
                } else {
                    // Extended format
                    $fieldConfigs[$field] = $config;
                }
            }

            /**
             * @var UrlHelper $urlHelper 
             */
            $urlHelper = $this->getContainer()->get(UrlHelper::class);

            // Set request on UrlHelper for proper base path and route result handling
            $urlHelper->setRequest($request);

            /**
             * @var ExcelExportHelper $exportHelper 
             */
            $exportHelper = $this->getContainer()->get(ExcelExportHelper::class);
            
            // Set ServerUrlHelper URI if available (for absolute URLs)
            if ($exportHelper->getServerUrlHelper() && $request->getUri()) {
                $exportHelper->getServerUrlHelper()->setUri($request->getUri());
            }

            $resultGenerator = $exportHelper->generateExportData($dqlQuery, $fields, $fieldConfigs);

            // Extract labels and types for ExcelResponse
            $captions = [];
            $fieldTypes = [];
            foreach ($fieldConfigs as $field => $config) {
                $captions[$field] = $config['label'] ?? $field;
                $fieldTypes[$field] = $config['excelType'] ?? 'text';
            }

            return new ExcelResponse(
                $resultGenerator,
                $captions,
                200,
                [
                    'Content-Disposition' => 'filename="export.xlsx"'
                ],
                $fieldTypes
            );
        }

        return new JsonResponse(
            array_map(
                function ($e) {
                    $obj = $this->getHydrator()->extract($e);
                    return $obj;
                },
                $dqlQuery->getResult()
            ),
            200,
            [
                'X-Total-Count' => $paginator->getTotalItemCount()
            ]
        );
    }

    public function export(array $records)
    {

    }

    public function getOneAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if ($request->getMethod() != 'GET') {
            return new JsonResponse([]);
        }

        if (!($entity = $this->getEntityManager()->find($this->getEntityName(), $request->getAttribute('entity_id')))) {
            return new JsonResponse([]);
        }
        /**
         * @var DoctrineEntity $hydrator 
         */
        $hydrator = $this->getHydrator();
        return new JsonResponse($hydrator->extract($entity));
    }

    public function createAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if ($request->getMethod() != 'POST') {
            return new JsonResponse([]);
        }

        $entityClass = $this->getEntityName();
        $entity = $this->getHydrator()->hydrate(
            $request->getParsedBody(),
            new $entityClass()
        );
        $this->getEntityManager()->persist($entity);
        $this->getEntityManager()->flush();

        return new JsonResponse($this->getHydrator()->extract($entity));
    }

    public function updateAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if ($request->getMethod() != 'PUT') {
            return new JsonResponse([]);
        }

        if (!($entity = $this->getEntityManager()->find($this->getEntityName(), $request->getAttribute('entity_id')))) {
            return $delegate->handle($request);
        }
        $entity = $this->getHydrator()->hydrate($this->getBodyParams()->getArrayCopy(), $entity);
        $this->getEntityManager()->flush();

        return new JsonResponse($this->getHydrator()->extract($entity));
    }

    public function deleteAction(ServerRequestInterface $request, RequestHandlerInterface $delegate): ResponseInterface
    {
        if ($request->getMethod() != 'DELETE') {
            return new JsonResponse([]);
        }

        if (!($entity = $this->getEntityManager()->find($this->getEntityName(), $request->getAttribute('entity_id')))) {
            return $delegate->handle($request);
        }
        $id = $this->getClassMetaData()->getIdentifierValues($entity);
        $this->getEntityManager()->remove($entity);
        $this->getEntityManager()->flush();

        return new JsonResponse($id);
    }



    /**
     * build where statement and add to the query builder
     *
     * @param \Doctrine\Orm\QueryBuilder $qb
     */
    protected function addOrderBy(QueryBuilder $qb, string $field, string $order = 'asc')
    {

        $parts = explode('.', $field);
        $par = 'e';

        $metaData = $this->getClassMetaData();
        foreach ($parts as $_k => $rel) {

            if ($metaData->hasAssociation($rel) && $_k + 1 < count($parts)) {
                $alias = strtolower($rel);
                $jt = new Expr\Join(Expr\Join::LEFT_JOIN, $par . '.' . $rel, $alias);
                if (!strpos($qb->getDql(), $jt->__toString()) !== false) {
                    $qb->leftJoin($par . '.' . $rel, $alias);
                }
                $par = $alias;
                $metaData = $this->getEntityManager()->getClassMetadata($metaData->getAssociationTargetClass($rel));

            } elseif ($metaData->hasField($rel)) {
                $qb->addOrderBy($par . '.' . $rel, $order);
            } else {
                break;
            }
        }
    }
}
