<?php

namespace ExprAs\Doctrine\View\Helper;

use Doctrine\ORM\EntityManager;
use DoctrineModule\Form\Element\ObjectSelect;
use Laminas\Filter\Word\UnderscoreToCamelCase;
use Laminas\View\Helper\AbstractHelper;

class SelectElement extends AbstractHelper
{
    protected $_entityManager;

    /**
     * AnalyticMainPageWidget constructor.
     *
     * @param EntityManager $_entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->_entityManager = $entityManager;
    }

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->_entityManager;
    }

    public function __invoke($name, $targetClass, $options = [], $property = null)
    {
        $options['target_class'] = $targetClass;
        $options['object_manager'] = $this->getEntityManager();
        if ($property) {
            $options['property'] = $property;
        } else {
            $options['label_generator'] = fn ($entity) => strval($entity);
        }

        $select = new ObjectSelect($name);
        $select->setOptions($options);

        $filter = new UnderscoreToCamelCase();
        foreach ($options as $_k => $_v) {
            $method = 'set' . $filter->filter($_k);
            if (method_exists($select, $method)) {
                call_user_func_array([$select, $method], [$_v]);
            }
        }



        return $this->getView()->formElement($select);
    }

}
