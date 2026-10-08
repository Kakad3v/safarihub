<?php

namespace App\Enums;

enum PackageType: string
{
    case Trip = 'trip';
    
    case Experience = 'experience';

    public function label(): string
    {
        return ucfirst($this->value);
    }
}
