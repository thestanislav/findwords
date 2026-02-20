<?php

namespace ExprAs\Rbac\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Entity\AbstractEntity;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Table(name: 'expras_rbac_roles')]
#[Gedmo\Tree(type: 'nested')]
#[ORM\Entity(repositoryClass: \ExprAs\Rbac\Repository\Role::class)]
class Role extends AbstractEntity implements \Stringable
{
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
     * @Gedmo\TreeRoot
     * @ORM\Column(name="root", type="integer", nullable=true)
     * @var                     integer
     */
    //protected $root;
    /**
     * @var Role
     */
    #[Gedmo\TreeParent]
    #[ORM\ManyToOne(targetEntity: \ExprAs\Rbac\Entity\Role::class, inversedBy: 'children')]
    #[ORM\JoinColumn(name: 'parent_role_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Role $parent = null;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 12, unique: true)]
    protected string $role_name;

    #[ORM\Column(type: 'string', length: 255)]
    protected string $label;


    /**
     * @var ArrayCollection
     */
    #[ORM\OneToMany(targetEntity: \ExprAs\Rbac\Entity\Role::class, mappedBy: 'parent')]
    #[ORM\OrderBy(['lft' => 'ASC'])]
    protected Collection $children;

    #[ORM\JoinTable(name: 'expras_rbac_role_permissions')]
    #[ORM\JoinColumn(name: 'role_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'perm_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToMany(targetEntity: \ExprAs\Rbac\Entity\Permission::class, inversedBy: 'roles', fetch: 'LAZY')]
    protected Collection $permissions;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->permissions = new ArrayCollection();
        $this->children = new ArrayCollection();
    }


    public function addPermissions(Permission $permission)
    {
        $this->permissions[] = $permission;
    }

    public function removePermission(Permission $permission)
    {
        return $this->permissions->removeElement($permission);
    }

    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @param string $role_name
     */
    public function setRoleName($role_name)
    {
        $this->role_name = $role_name;
    }

    /**
     * @return string
     */
    public function getRoleName(): string
    {
        return $this->role_name;
    }

    public function getName(): string
    {
        return $this->getRoleName();
    }


    public function getLft()
    {
        return $this->lft;
    }

    public function setLft($lft)
    {
        $this->lft = $lft;
    }

    public function getLevel()
    {
        return $this->level;
    }

    public function setLevel($lvl)
    {
        $this->level = $lvl;
    }

    public function getRgt()
    {
        return $this->rgt;
    }

    public function setRgt($rgt)
    {
        $this->rgt = $rgt;
    }


    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(?Role $parent): void
    {
        $this->parent = $parent;
    }

    public function getChildren(): iterable
    {
        return $this->children;
    }

    public function hasChildren(): bool
    {
        return (bool)count($this->children);
    }

    public function setChildren($children)
    {
        $this->children = $children;
    }

    public function setLabel(mixed $label)
    {
        $this->label = $label;
    }

    /**
     * @return mixed
     */
    public function getLabel()
    {
        return $this->label;
    }

    public function hasPermission(string $permission): bool
    {
        $result = $this->getPermissions()->filter(fn (Permission $Permission) => $Permission->getPermName() == $permission);
        //$criteria = Criteria::create()->where(Criteria::expr()->eq('perm_name', (string)$permission));
        //$result = $this->permissions->matching($criteria);

        return $result->count() > 0;
    }

    public function __toString(): string
    {
        return (string) $this->getLabel();
    }
}
