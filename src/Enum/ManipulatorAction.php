<?php

namespace App\Enum;

enum ManipulatorAction: string {
    case Include = 'include';
    case Exclude = 'exclude';
}