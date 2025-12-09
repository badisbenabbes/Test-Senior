<?php

declare(strict_types=1);

namespace App\App\Handler;

use App\App\Dto\RegisterVehicleInputDTO;
use App\App\Repository\FleetRepositoryInterface;
use App\Domain\Fleet\FleetId;
use App\Domain\Vehicle\VehiclePlateNumber;
use RuntimeException;

final readonly class RegisterVehicleHandler
{
    public function __construct(private FleetRepositoryInterface $repo)
    {
    }

    public function __invoke(RegisterVehicleInputDTO $registerVehicleInput): void
    {
        $fleetId = new FleetId($registerVehicleInput->fleetId);
        $plateNumber = new VehiclePlateNumber($registerVehicleInput->plate);

        $fleet = $this->repo->get($fleetId);
        if (null === $fleet) {
            throw new RuntimeException(sprintf('Fleet "%s" not found', $registerVehicleInput->fleetId));
        }

        $fleet->registerVehicle($plateNumber);

        $this->repo->addVehicle($fleet?->id(), $plateNumber);
    }
}
