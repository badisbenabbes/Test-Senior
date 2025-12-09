<?php

declare(strict_types=1);

namespace App\App\Dto;

final readonly class ParkVehicleInputDTO
{
    public function __construct(
        public string $fleetId,
        public string $plate,
        public float  $lat,
        public float  $lng,
        public ?float $alt = null,
    ) {}
}
