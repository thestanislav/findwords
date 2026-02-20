<?php

namespace ExprAs\Rbac\Entity;

use Doctrine\Common\Collections\Collection;
use ExprAs\Rest\Entity\AbstractEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'expras_rbac_permissions')]
#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: \ExprAs\Rbac\Repository\Permission::class)]
class Permission extends AbstractEntity
{
    /**
     * @var string
     */
    #[ORM\Column(type: 'string', unique: true, length: 32)]
    protected string $perm_name;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', nullable: true, length: 128)]
    protected ?string $perm_description = null;


    /**
     * @var integer
     */
    #[Gedmo\TreeLeft]
    #[ORM\Column(name: 'lft', type: 'integer')]
    protected int $lft;

    /**
     * @var integer
     */
    #[Gedmo\TreeLevel]
    #[ORM\Column(name: 'lvl', type: 'integer')]
    protected int $level;

    /**
     * @var integer
     */
    #[Gedmo\TreeRight]
    #[ORM\Column(name: 'rgt', type: 'integer')]
    protected int $rgt;



    /**
     * @var Role
     */
    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: \ExprAs\Rbac\Entity\Permission::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_perm_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Role $parent = null;

    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \ExprAs\Rbac\Entity\Permission::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected Collection $children;

    #[ORM\ManyToMany(targetEntity: \ExprAs\Rbac\Entity\Role::class, mappedBy: 'permissions')]
    protected Collection $roles;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
        $this->children = new ArrayCollection();
    }

    /**
     * Add roles
     *
     * @return Permission
     */
    public function addRole(Role $user)
    {
        $this->roles[] = $user;

        return $this;
    }

    /**
     * Remove roles
     */
    public function removeRole(Role $user)
    {
        $this->roles->removeElement($user);
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRoles()
    {
        return $this->roles;
    }


    /**
     * @param string|null $perm_description
     */
    public function setPermDescription(?string $perm_description): void
    {
        $this->perm_description = $perm_description;
    }

    /**
     * @return string
     */
    public function getPermDescription(): ?string
    {
        return $this->perm_description;
    }

    /**
     * @param string $perm_name
     */
    public function setPermName($perm_name): void
    {
        $this->perm_name = $perm_name;
    }

    /**
     * @return string
     */
    public function getPermName(): string
    {
        return $this->perm_name;
    }


    public function getId(): int
    {
        return (int) $this->id;
    }

    public function getLft(): int
    {
        return $this->lft;
    }

    public function setLft(int $lft): void
    {
        $this->lft = $lft;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $lvl): void
    {
        $this->level = $lvl;
    }

    public function getRgt(): int
    {
        return $this->rgt;
    }

    public function setRgt(int $rgt): void
    {
        $this->rgt = $rgt;
    }



    public function getParent(): ?Role
    {
        return $this->parent;
    }

    public function setParent(?Role $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren()
    {
        return $this->children;
    }

    public function hasChildren()
    {
        return (bool) count($this->children);
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }
}
