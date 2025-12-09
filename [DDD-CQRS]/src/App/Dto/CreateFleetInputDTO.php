<?php

declare(strict_types=1);

namespace App\App\Dto;

final readonly class CreateFleetInputDTO
{
    public function __construct(
        public string $userId
    ) {}
}
