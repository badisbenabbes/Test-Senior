<?php

declare(strict_types=1);

namespace App\Domain\Vehicle;

use InvalidArgumentException;

final readonly class VehiclePlateNumber
{
    public string $value;

    public function __construct(string $value)
    {
        $value = strtoupper(trim($value));
        if ('' === $value) {
            throw new InvalidArgumentException('VehiclePlateNumber cannot be empty');
        }
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
