<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 */
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\OneToMany(targetEntity=Group::class, mappedBy="leader", orphanRemoval=true)
     */
    private $groupsInWhichUserLeader;

    /**
     * @ORM\ManyToMany(targetEntity=Group::class, inversedBy="members")
     * @ORM\JoinTable(name="users_groups")
     */
    private $groupsInWhichUserMember;

    public function __construct()
    {
        $this->groupsInWhichUserLeader = new ArrayCollection();
        $this->groupsInWhichUsermember = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Group[]
     */
    public function getGroupsInWhichUserLeader(): Collection
    {
        return $this->groupsInWhichUserLeader;
    }

    public function addGroupInWhichUserLeader(Group $groupsInWhichUserLeader): self
    {
        if (!$this->groupsInWhichUserLeader->contains($groupsInWhichUserLeader)) {
            $this->groupsInWhichUserLeader[] = $groupsInWhichUserLeader;
            $groupsInWhichUserLeader->setLeader($this);
        }

        return $this;
    }

    public function removeGroupInWhichUserLeader(Group $groupsInWhichUserLeader): self
    {
        if ($this->groupsInWhichUserLeader->removeElement($groupsInWhichUserLeader)) {
            // set the owning side to null (unless already changed)
            if ($groupsInWhichUserLeader->getLeader() === $this) {
                $groupsInWhichUserLeader->setLeader(null);
            }
        }

        return $this;
    }

    public static function createUser($username, $password, UserPasswordHasherInterface $hasher)
    {
        $user = new User();
        $user->setUsername($username);
        $user->setPassword($hasher->hashPassword($user, $password));
        return $user;
    }

    public function getGroupsInWhichUserMember()
    {
        return $this->getGroupsInWhichUserMember;
    }

    public function joinGroup(Group $group)
    {
        $group->addMember($this);
        $this->groupsInWhichUserMember[] = $group;
    }

    public function leaveGroup(Group $group)
    {
        $group->removeMember($this);
        $this->groupsInWhichUserMember->removeElement($group);
    }

    public function disbandGroup(Group $group)
    {
        $this->groupsInWhichUserLeader->removeElement($group);
    }

}
