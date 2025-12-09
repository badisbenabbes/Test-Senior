<?php

declare(strict_types=1);

namespace App\Infra\InMemory;

use App\App\Repository\FleetRepositoryInterface;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Vehicle\VehicleLocation;
use App\Domain\Vehicle\VehiclePlateNumber;
use RuntimeException;

/**
 * @deprecated This in-memory repository is no longer used. The project now relies on FleetRepository.
 *             Instantiating this class will throw to prevent accidental usage.
 */
final class InMemoryFleet implements FleetRepositoryInterface
{
    public function __construct()
    {
        throw new RuntimeException('InMemoryFleet is deprecated. Use FleetRepository instead.');
    }
    /**
     * @var array<string, Fleet>
     */
    private array $store = [];

    public function createFleet(FleetId $id): void
    {
        throw $this->deprecated();
    }

    public function addVehicle(FleetId $id, VehiclePlateNumber $plate): void
    {
        throw $this->deprecated();
    }

    public function updateVehicleLocation(
        FleetId $id,
        VehiclePlateNumber $plate,
        VehicleLocation $location
    ): void {
        throw $this->deprecated();
    }

    public function get(FleetId $id): Fleet
    {
        throw $this->deprecated();
    }

    private function deprecated(): RuntimeException
    {
        return new RuntimeException(
            'InMemoryFleetRepository is deprecated. Use FleetRepository instead.'
        );
    }
}
