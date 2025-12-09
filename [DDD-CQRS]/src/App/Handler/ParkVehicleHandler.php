<?php

declare(strict_types=1);

namespace App\App\Handler;

use App\App\Dto\ParkVehicleInputDTO;
use App\App\Repository\FleetRepositoryInterface;
use App\Domain\Fleet\FleetId;
use App\Domain\Vehicle\VehicleLocation;
use App\Domain\Vehicle\VehiclePlateNumber;
use RuntimeException;

final readonly class ParkVehicleHandler
{
    public function __construct(private FleetRepositoryInterface $repo)
    {
    }

    public function __invoke(ParkVehicleInputDTO $parkVehicleInput): void
    {
        $fleetId = new FleetId($parkVehicleInput->fleetId);
        $plateNumber = new VehiclePlateNumber($parkVehicleInput->plate);
        $location = new VehicleLocation($parkVehicleInput->lat, $parkVehicleInput->lng, $parkVehicleInput->alt);

        $fleet = $this->repo->get($fleetId);

        if (null === $fleet) {
            throw new RuntimeException(sprintf('Fleet "%s" not found', $parkVehicleInput->fleetId));
        }

        $fleet->parkVehicle($plateNumber, $location);

        $this->repo->updateVehicleLocation($fleet->id(), $plateNumber, $location);
    }
}
