<?php

declare(strict_types=1);

namespace App\App\Handler;

use App\App\Dto\CreateFleetInputDTO;
use App\App\Repository\FleetRepositoryInterface;
use App\Domain\Fleet\FleetId;

final readonly class CreateFleetHandler
{
    public function __construct(private FleetRepositoryInterface $fleetRepository)
    {
    }

    public function __invoke(CreateFleetInputDTO $createFleetInput): FleetId
    {
        $fleetId = new FleetId($createFleetInput->userId);
        $this->fleetRepository->createFleet($fleetId);

        return $fleetId;
    }
}
