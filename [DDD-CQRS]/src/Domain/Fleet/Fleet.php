<?php

declare(strict_types=1);

namespace App\Domain\Fleet;

use App\Domain\Exceptions\VehicleAlreadyInFleetException;
use App\Domain\Exceptions\VehicleAlreadyParkedInThisLocation;
use App\Domain\Exceptions\VehicleNotInFleetException;
use App\Domain\Vehicle\VehicleLocation;
use App\Domain\Vehicle\VehiclePlateNumber;

final class Fleet
{
    /**
     * @var array<string, VehicleLocation|null>
     */
    private array $vehicles = [];

    public function __construct(
        private readonly FleetId $id,
    ) {
    }

    public function id(): FleetId
    {
        return $this->id;
    }

    /**
     * @throws VehicleAlreadyInFleetException
     */
    public function registerVehicle(VehiclePlateNumber $plate): void
    {
        $key = (string) $plate;

        if ($this->hasVehicle($plate)) {
            throw new VehicleAlreadyInFleetException("Vehicle {$plate} is already in this fleet");
        }

        $this->vehicles[$key] = null;
    }

    /**
     * @throws VehicleNotInFleetException
     * @throws VehicleAlreadyParkedInThisLocation
     */
    public function parkVehicle(VehiclePlateNumber $plate, VehicleLocation $location): void
    {
        $key = (string) $plate;

        if (!$this->hasVehicle($plate)) {
            throw new VehicleNotInFleetException("Vehicle {$plate} not in fleet");
        }

        $currentLocation = $this->vehicles[$key];

        if ($currentLocation !== null && $currentLocation->equals($location)) {
            throw new VehicleAlreadyParkedInThisLocation("Vehicle {$plate} is already parked at this location");
        }

        $this->vehicles[$key] = $location;
    }

    public function hasVehicle(VehiclePlateNumber $plate): bool
    {
        return array_key_exists((string) $plate, $this->vehicles);
    }

    public function getVehicleLocation(VehiclePlateNumber $plate): ?VehicleLocation
    {
        return $this->vehicles[(string) $plate] ?? null;
    }

    /**
     * @return array<string, VehicleLocation|null>
     */
    public function vehicles(): array
    {
        return $this->vehicles;
    }
}
