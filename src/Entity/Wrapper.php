<?php

namespace App\Entity;

use App\Repository\WrapperRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: WrapperRepository::class)]
class Wrapper
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?Uuid $id;

    #[ORM\Column(length: 255)]
    private ?string $feed = null;

    #[ORM\OneToMany(mappedBy: 'wrapper', targetEntity: Manipulator::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $manipulators;

    #[ORM\ManyToOne(inversedBy: 'wrappers')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    public function __construct()
    {
        $this->manipulators = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getFeed(): ?string
    {
        return $this->feed;
    }

    public function setFeed(string $feed): static
    {
        $this->feed = $feed;

        return $this;
    }

    /**
     * @return Collection<int, Manipulator>
     */
    public function getManipulators(): Collection
    {
        return $this->manipulators;
    }

    public function addManipulator(Manipulator $manipulator): static
    {
        if (!$this->manipulators->contains($manipulator)) {
            $this->manipulators->add($manipulator);
            $manipulator->setWrapper($this);
        }

        return $this;
    }

    public function removeManipulator(Manipulator $manipulator): static
    {
        if ($this->manipulators->removeElement($manipulator)) {
            // set the owning side to null (unless already changed)
            if ($manipulator->getWrapper() === $this) {
                $manipulator->setWrapper(null);
            }
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }
}
