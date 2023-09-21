<?php

namespace App\Entity;

use App\Enum\ManipulatorAction;
use App\Enum\ManipulatorType;
use App\Repository\ManipulatorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ManipulatorRepository::class)]
class Manipulator
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, enumType: ManipulatorType::class)]
    private ?ManipulatorType $type = null;

    #[ORM\Column(length: 255)]
    private ?string $field = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $value = null;

    #[ORM\Column(length: 255, enumType: ManipulatorAction::class)]
    private ?ManipulatorAction $action = null;

    #[ORM\ManyToOne(inversedBy: 'manipulators')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Wrapper $wrapper = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?ManipulatorType
    {
        return $this->type;
    }

    public function setType(ManipulatorType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getField(): ?string
    {
        return $this->field;
    }

    public function setField(string $field): static
    {
        $this->field = $field;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): static
    {
        $this->value = $value;

        return $this;
    }

    public function getAction(): ?ManipulatorAction
    {
        return $this->action;
    }

    public function setAction(ManipulatorAction $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getWrapper(): ?Wrapper
    {
        return $this->wrapper;
    }

    public function setWrapper(?Wrapper $wrapper): static
    {
        $this->wrapper = $wrapper;

        return $this;
    }
}
