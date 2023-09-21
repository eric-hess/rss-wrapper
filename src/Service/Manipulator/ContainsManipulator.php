<?php

namespace App\Service\Manipulator;

use App\Entity\Manipulator;
use App\Enum\ManipulatorAction;
use App\Enum\ManipulatorType;
use SimpleXMLElement;

class ContainsManipulator implements ManipulatorInterface
{
    public function supports(ManipulatorType $type): bool
    {
        return $type === ManipulatorType::Contains;
    }

    public function shouldElementBeRemoved(SimpleXMLElement $element, Manipulator $manipulator): bool
    {
        if (!$this->supports($manipulator->getType())) {
            throw new \InvalidArgumentException();
        }

        $contains = str_contains($element->{$manipulator->getField()}, $manipulator->getValue());

        if ($manipulator->getAction() === ManipulatorAction::Include) {
            return !$contains;
        } else if ($manipulator->getAction() === ManipulatorAction::Exclude) {
            return $contains;
        }

        return false;
    }
}