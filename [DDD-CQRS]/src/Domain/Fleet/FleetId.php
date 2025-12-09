<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use InvalidArgumentException;

final class FleetId
{
    public string $value;

    public function __construct(string $value)
    {
        $value = trim($value);

        if ('' === $value) {
            throw new InvalidArgumentException('FleetId cannot be empty');
        }

        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function equals(FleetId $other): bool
    {
        return $this->value === $other->value;
    }
}
