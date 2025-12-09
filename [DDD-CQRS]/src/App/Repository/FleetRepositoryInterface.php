<?php

declare(strict_types=1);

namespace App\App\Repository;

use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Vehicle\VehicleLocation;
use App\Domain\Vehicle\VehiclePlateNumber;

interface FleetRepositoryInterface
{
    /**
     * Create an empty fleet row if not exists
     */
    public function createFleet(FleetId $id): void;

    /**
     * Persist the registration of a vehicle into the fleet
     */
    public function addVehicle(FleetId $id, VehiclePlateNumber $plate): void;

    /**
     * Persist the current location of a vehicle in the fleet
     */
    public function updateVehicleLocation(
        FleetId $id,
        VehiclePlateNumber $plate,
        VehicleLocation $location
    ): void;

    /**
     * Retrieve a Fleet aggregate by id, or throw if not found
     */
    public function get(FleetId $id): ?Fleet;
}
