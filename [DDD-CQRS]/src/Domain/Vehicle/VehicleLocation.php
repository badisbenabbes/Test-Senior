<?php

declare(strict_types=1);

namespace App\Domain\Vehicle;

final readonly class VehicleLocation
{
    public float $lat;
    public float $lng;
    public ?float $alt;

    public function __construct(float $lat, float $lng, ?float $alt = null)
    {
        $this->lat = $lat;
        $this->lng = $lng;
        $this->alt = $alt;
    }

    public function equals(self $vehicleLocation, float $epsilon = 1e-6): bool
    {
        return $this->nearlyEqual($this->lat, $vehicleLocation->lat, $epsilon)
            && $this->nearlyEqual($this->lng, $vehicleLocation->lng, $epsilon)
            && $this->equalAltitude($vehicleLocation, $epsilon);
    }

    public function __toString(): string
    {
        return sprintf(
            'lat: %F, lng: %F, alt: %s',
            $this->lat,
            $this->lng,
            null !== $this->alt ? (string) $this->alt : 'null'
        );
    }

    private function nearlyEqual(float $a, float $b, float $epsilon): bool
    {
        return abs($a - $b) <= $epsilon;
    }

    private function equalAltitude(VehicleLocation $vehicleLocation, float $epsilon): bool
    {
        if (null === $this->alt || null === $vehicleLocation->alt) {
            return $this->alt === $vehicleLocation->alt;
        }

        return $this->nearlyEqual($this->alt, $vehicleLocation->alt, $epsilon);
    }
}
