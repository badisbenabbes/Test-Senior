<?php

declare(strict_types=1);

namespace App\Infra\Repository;

use App\App\Repository\FleetRepositoryInterface;
use App\Domain\Exceptions\FleetNotFoundException;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Vehicle\VehicleLocation;
use App\Domain\Vehicle\VehiclePlateNumber;
use PDO;

final readonly class FleetRepository implements FleetRepositoryInterface
{
    public function __construct(private PDO $pdo)
    {
    }

    public function createFleet(FleetId $id): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO fleet (id) VALUES (:id) 
            ON CONFLICT (id) DO NOTHING'
        );

        $stmt->execute(['id' => (string) $id]);
    }

    public function addVehicle(FleetId $id, VehiclePlateNumber $plate): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO fleet_vehicle (fleet_id, plate, lat, lng, alt) 
            VALUES (:fleet_id, :plate, NULL, NULL, NULL)
            ON CONFLICT (fleet_id, plate) DO NOTHING'
        );

        $stmt->execute([
            'fleet_id' => (string) $id,
            'plate' => (string) $plate,
        ]);
    }

    public function updateVehicleLocation(
        FleetId $id,
        VehiclePlateNumber $plate,
        VehicleLocation $location
    ): void {
        $stmt = $this->pdo->prepare(
            'UPDATE fleet_vehicle 
            SET lat = :lat, lng = :lng, alt = :alt
            WHERE fleet_id = :fleet_id AND plate = :plate
        ');

        $stmt->execute([
            'lat' => $location->lat,
            'lng' => $location->lng,
            'alt' => $location->alt,
            'fleet_id' => (string) $id,
            'plate' => (string) $plate,
        ]);
    }

    public function get(FleetId $id): ?Fleet
    {
        $this->assertFleetExists($id);
        $fleet = new Fleet($id);

        $stmt = $this->pdo->prepare(
            'SELECT plate, lat, lng, alt 
            FROM fleet_vehicle 
            WHERE fleet_id = :fleet'
        );

        $stmt->execute(['fleet' => (string)$id]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $plate = new VehiclePlateNumber($row['plate']);
            $fleet->registerVehicle($plate);

            if (null !== $row['lat'] && null !== $row['lng']) {
                $altitude = $row['alt'] !== null ? (float) $row['alt'] : null;
                $location = new VehicleLocation(
                    (float) $row['lat'],
                    (float) $row['lng'],
                    $altitude
                );

                $fleet->parkVehicle($plate, $location);
            }
        }

        return $fleet;
    }

    /**
     * @throws FleetNotFoundException
     */
    private function assertFleetExists(FleetId $id): void
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM fleet WHERE id = :id'
        );

        $stmt->execute(['id' => (string) $id]);

        if ($stmt->fetchColumn() === false) {
            throw new FleetNotFoundException(sprintf('Fleet %s not found', $id));
        }
    }
}
