<?php
/**
 * Author: Pavel Zarubov <zara@fu2re.ru>
 * Date: 13.04.2014
 * Time: 0:01
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Entity\AbstractEntity;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity]
#[ORM\Table(name: 'dictionaries')]
class Dictionary extends AbstractEntity
{
    #[ORM\Column(type: 'string')]
    protected $title;

    #[ORM\Column(type: 'string', length: 32, unique: true)]
    protected $definedName;

    #[ORM\Column(type: 'string', length: 2)]
    protected $lang;

    #[ORM\Column(type: 'integer', name: 'sort_order')]
    #[Gedmo\SortablePosition]
    protected $sortOrder;

    #[ORM\OneToMany(targetEntity: Definition::class, mappedBy: 'dictionary', cascade: ['persist', 'remove', 'merge'], orphanRemoval: true, fetch: 'EXTRA_LAZY')]
    protected $definitions;

    public function __construct()
    {
        $this->definitions = new ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getDefinedName()
    {
        return $this->definedName;
    }

    /**
     * @param mixed $definedName
     */
    public function setDefinedName($definedName)
    {
        $this->definedName = $definedName;
    }

    /**
     * @return mixed
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param mixed $lang
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return mixed
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @param mixed $sortOrder
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    }

    /**
     * @return mixed
     */
    public function getDefinitions()
    {
        return $this->definitions;
    }

    /**
     * @param mixed $definitions
     */
    public function setDefinitions($definitions)
    {
        $this->definitions = $definitions;
    }


    /**
     * @param $definitions
     *
     * @return array|\Traversable
     */
    public function addDefinitions($definitions)
    {
        if (is_array($definitions) || $definitions instanceof \Traversable) {
            foreach ($definitions as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
        }
        return $this->definitions[] = $definitions;
    }

    /**
     * @param $definitions
     *
     * @return array|\Traversable
     */
    public function removeDefinitions($definitions)
    {
        if (is_array($definitions) || $definitions instanceof \Traversable) {
            foreach ($definitions as $_entity) {
                call_user_func_array(array($this, __FUNCTION__), array($_entity));
            }
        }
        return $this->definitions->removeElement($definitions);
    }

}
