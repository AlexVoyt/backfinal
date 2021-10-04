<?php

namespace App\Entity;

use App\Repository\GroupRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity(repositoryClass=GroupRepository::class)
 * @ORM\Table(name="`group`")
 */
class Group
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $motd;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="groupsInWhichUserLeader")
     * @ORM\JoinColumn(nullable=false)
     */
    private $leader;

     /**
     * @ORM\ManyToMany(targetEntity=User::class, mappedBy="groupsInWhichUserMember")
     * 
     */
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMotd(): ?string
    {
        return $this->motd;
    }

    public function setMotd(?string $motd): self
    {
        $this->motd = $motd;

        return $this;
    }

    public function getLeader(): ?User
    {
        return $this->leader;
    }

    public function setLeader(?User $leader): self
    {
        $leader->joinGroup($this);
        $this->leader = $leader;

        return $this;
    }

    public function getMembers()
    {
        return $this->members;
    }

    public function addMember(User $member)
    {
        $this->members[] = $member;
    }

    public function removeMember(User $member)
    {
        $this->members->removeElement($member);
    }
}
