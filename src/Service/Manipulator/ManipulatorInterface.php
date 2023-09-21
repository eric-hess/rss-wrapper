<?php

namespace App\Service\Manipulator;

use App\Entity\Manipulator;
use App\Enum\ManipulatorType;
use SimpleXMLElement;

interface ManipulatorInterface
{
    public function supports(ManipulatorType $type): bool;
    public function shouldElementBeRemoved(SimpleXMLElement $element, Manipulator $manipulator): bool;
}