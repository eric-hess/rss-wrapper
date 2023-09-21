<?php

namespace App\Enum;

enum ManipulatorType: string {
    case Contains = 'contains';
    case DoesNotContains = 'doesNotContains';
}