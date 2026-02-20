<?php

namespace ExprAs\User\Entity;

use Doctrine\Common\Collections\Collection;
use ExprAs\Doctrine\Behavior\Timestampable\TimestampableTrait;
use ExprAs\Rbac\Entity\Role;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ExprAs\Rest\Entity\AbstractEntity;
use Mezzio\Authentication\UserInterface;

#[ORM\MappedSuperclass]
class UserSuper extends AbstractEntity implements UserInterface, \Stringable
{
    use TimestampableTrait;


    /**
     * @var Collection
     */
    #[ORM\JoinTable(name: 'expras_user_roles')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'role_id', referencedColumnName: 'id')]
    #[ORM\ManyToMany(targetEntity: Role::class)]
    protected Collection|ArrayCollection $rbacRoles;

    /**
     * @var string
     */
    #[ORM\Column(name: 'username', type: 'string', length: 128, unique: true, nullable: false)]
    protected string $username;

    /**
     * @var string
     */
    #[ORM\Column(name: 'email', type: 'string', length: 128, unique: true, nullable: false)]
    protected string $email;

    /**
     * @var ?string
     */
    #[ORM\Column(name: 'display_name', type: 'string', length: 50, nullable: true)]
    protected ?string $displayName = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'password', type: 'string', length: 128)]
    protected string $password;

    /**
     * @var boolean
     */
    #[ORM\Column(name: 'state', type: 'boolean', nullable: true)]
    protected bool $active = false;

    /**
     * @var ?Profile
     */
    #[ORM\OneToOne(targetEntity: \ExprAs\User\Entity\Profile::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: true)]
    protected ?Profile $profile = null;

    /**
     * @var ?\DateTimeImmutable
     */
    #[ORM\Column(name: 'last_login_at', type: 'datetime_immutable', nullable: true)]
    protected ?\DateTimeImmutable $lastLoginAt = null;

    /**
     * @var ?\DateTimeImmutable
     */
    #[ORM\Column(name: 'last_activity_at', type: 'datetime_immutable', nullable: true)]
    protected ?\DateTimeImmutable $lastActivityAt = null;



    /**
     * Constructor
     */
    public function __construct()
    {
        $this->rbacRoles = new ArrayCollection();

    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }


    public function getIdentity(): string
    {
        return $this->getUsername();
    }


    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     *
     * @return UserSuper
     */
    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserSuper
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set displayName
     *
     * @param string $displayName
     *
     * @return UserSuper
     */
    public function setDisplayName(string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    /**
     * Get displayName
     *
     * @return string
     */
    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    /**
     * Set password
     *
     * @param string $password
     *
     * @return UserSuper
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set state
     *
     * @param boolean $active
     *
     * @return UserSuper
     */
    public function setActive($active = true)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get state
     *
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }


    /**
     * Add roles
     *
     * @param Role $roles
     *
     * @return UserSuper
     */
    public function addRbacRoles($roles)
    {
        if (is_iterable($roles)) {
            foreach ($roles as $_c) {
                $this->addRbacRoles($_c);
            }
            return $this;
        }
        if (!$this->rbacRoles->contains($roles)) {
            $this->rbacRoles[] = $roles;
        }

        return $this;
    }

    /**
     * Remove roles
     *
     * @param Role $roles
     */
    public function removeRbacRoles($roles)
    {
        if ($roles instanceof \Traversable) {
            foreach ($roles as $_c) {
                $this->removeRbacRoles($_c);
            }
            return $this;
        }
        $this->rbacRoles->removeElement($roles);
        return $this;
    }

    /**
     * Get roles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRbacRoles()
    {
        return $this->rbacRoles;
    }

    public function getRoles(): array
    {
        return $this->_getRoleNames($this->getRbacRoles());
    }

    protected function _getRoleNames($rbacRoles)
    {
        $roles = [];
        /**
 * @var Role $_role 
*/
        foreach ($rbacRoles as $_role) {
            $roles[] = $_role->getRoleName();
            $roles = array_merge($roles, $this->_getRoleNames($_role->getChildren()));
        }

        return array_unique($roles);
    }

    public function setRbacRoles($rbacRoles)
    {
        $this->rbacRoles = $rbacRoles;
    }

    public function hasRole($name)
    {
        return $this->getRbacRoles()->exists(
            function ($i, $role) use ($name) {
                if ($role->getRoleName() == $name) {
                    return true;
                }
            }
        );
    }

    public function getDetail(string $name, $default = null)
    {
        return $this->getDisplayName() ?: $default;
    }

    public function __toString(): string
    {
        return ($this->getDisplayName() ?: $this->getUsername()) ?: $this->getEmail();
    }

    public function getDetails(): array
    {
        return [];
    }

    /**
     * @return Profile
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * @param Profile $profile
     */
    public function setProfile(?Profile $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastLoginAt(): ?\DateTimeImmutable
    {
        return $this->lastLoginAt;
    }

    /**
     * @param \DateTimeImmutable|null $lastLoginAt
     */
    public function setLastLoginAt(?\DateTimeImmutable $lastLoginAt): void
    {
        $this->lastLoginAt = $lastLoginAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function getLastActivityAt(): ?\DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    /**
     * @param \DateTimeImmutable|null $lastActivityAt
     */
    public function setLastActivityAt(?\DateTimeImmutable $lastActivityAt): void
    {
        $this->lastActivityAt = $lastActivityAt;
    }


}
