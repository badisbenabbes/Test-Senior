<?php

declare(strict_types=1);

namespace App\App\Dto;

final readonly class RegisterVehicleInputDTO
{
    public function __construct(
        public string $fleetId,
        public string $plate
    ) {}
}
