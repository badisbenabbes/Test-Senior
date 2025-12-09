<?php

declare(strict_types=1);

namespace Tests\Domain\Fleet;

use App\Domain\Exceptions\VehicleAlreadyInFleetException;
use App\Domain\Exceptions\VehicleAlreadyParkedInThisLocation;
use App\Domain\Exceptions\VehicleNotInFleetException;
use App\Domain\Fleet\Fleet;
use App\Domain\Fleet\FleetId;
use App\Domain\Vehicle\VehicleLocation;
use App\Domain\Vehicle\VehiclePlateNumber;
use PHPUnit\Framework\TestCase;

final class FleetTest extends TestCase
{
    private const string USER_ID  = 'user-123';
    private const string PLATE  = 'ABC-123';
    private Fleet $fleet;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fleet = new Fleet(new FleetId(self::USER_ID));
    }

    public function testIdReturnsGivenId(): void
    {
        $id = new FleetId(self::USER_ID);
        $fleet = new Fleet($id);

        self::assertSame($id, $fleet->id());
        self::assertTrue($fleet->id()->equals($id));
    }

    /**
     * @dataProvider vehiclePlateProvider
     */
    public function testRegisterVehicleAddsVehicleAndInitialLocationIsNull(string $plateNumber): void
    {
        $plate = new VehiclePlateNumber($plateNumber);

        $this->fleet->registerVehicle($plate);

        self::assertTrue($this->fleet->hasVehicle($plate));
        self::assertNull($this->fleet->getVehicleLocation($plate));

        $vehicles = $this->fleet->vehicles();

        self::assertArrayHasKey((string) $plate, $vehicles);
        self::assertCount(1, $vehicles);
    }

    /**
     * @return array<string, array{string}>
     */
    public static function vehiclePlateProvider(): array
    {
        return [
            'standard format'   => [self::PLATE],
            'with numbers only' => ['999-AAA'],
            'mixed case'        => ['abC-789'],
        ];
    }

    public function testRegisteringSameVehicleTwiceThrows(): void
    {
        $plate = new VehiclePlateNumber(self::PLATE);

        $this->fleet->registerVehicle($plate);

        $this->expectException(VehicleAlreadyInFleetException::class);

        $this->fleet->registerVehicle($plate);
    }

    /**
     * @dataProvider unknownVehicleAndLocationProvider
     */
    public function testParkVehicleNotInFleetThrows(string $plateNumber, float $lat, float $lng, float $alt): void
    {
        $plate = new VehiclePlateNumber($plateNumber);
        $loc = new VehicleLocation($lat, $lng, $alt);

        $this->expectException(VehicleNotInFleetException::class);

        $this->fleet->parkVehicle($plate, $loc);
    }

    /**
     * @return array<string, array{string, float, float, float}>
     */
    public static function unknownVehicleAndLocationProvider(): array
    {
        return [
            'paris missing'   => ['MISSING-1', 48.8566, 2.3522, 35.0],
            'toulouse missing'=> ['UNKNOWN-2', 43.6045, 1.4442, 150.0],
        ];
    }

    /**
     * @dataProvider vehicleLocationProvider
     */
    public function testParkVehicleSetsLocation(
        string $plateNumber,
        float $lat,
        float $lng,
        float $alt
    ): void {
        $plate = new VehiclePlateNumber($plateNumber);
        $this->fleet->registerVehicle($plate);

        $loc = new VehicleLocation($lat, $lng, $alt);
        $this->fleet->parkVehicle($plate, $loc);

        $stored = $this->fleet->getVehicleLocation($plate);

        self::assertNotNull($stored);
        self::assertTrue($stored->equals($loc));
        self::assertTrue($this->fleet->hasVehicle($plate));
    }

    /**
     * @return array<string, array{string, float, float, float}>
     */
    public static function vehicleLocationProvider(): array
    {
        return [
            'paris low altitude'   => [self::PLATE, 48.8566, 2.3522, 35.0],
            'toulouse mid altitude'=> ['XYZ-999', 43.6045, 1.4442, 150.0],
        ];
    }

    public function testParkingTwiceAtSameLocationThrows(): void
    {
        $plate = new VehiclePlateNumber(self::PLATE);
        $this->fleet->registerVehicle($plate);

        $loc = new VehicleLocation(48.8566, 2.3522, 35.0);
        $this->fleet->parkVehicle($plate, $loc);

        $this->expectException(VehicleAlreadyParkedInThisLocation::class);

        $this->fleet->parkVehicle($plate, $loc);
    }

    /**
     * @dataProvider twoDifferentLocationsProvider
     */
    public function testParkingAtDifferentLocationUpdates(
        float $lat1,
        float $lng1,
        float $alt1,
        float $lat2,
        float $lng2,
        float $alt2
    ): void {
        $plate = new VehiclePlateNumber(self::PLATE);
        $this->fleet->registerVehicle($plate);

        $loc1 = new VehicleLocation($lat1, $lng1, $alt1);
        $loc2 = new VehicleLocation($lat2, $lng2, $alt2);

        $this->fleet->parkVehicle($plate, $loc1);
        $this->fleet->parkVehicle($plate, $loc2);

        $stored = $this->fleet->getVehicleLocation($plate);

        self::assertNotNull($stored);
        self::assertTrue($stored->equals($loc2), 'Expected stored location to be the last one');
        self::assertFalse($stored->equals($loc1), 'Stored location should not be the old one');
    }

    /**
     * @return array<string, array{float, float, float, float, float, float}>
     */
    public static function twoDifferentLocationsProvider(): array
    {
        return [
            'paris-to-toulouse' => [48.8566, 2.3522, 35.0, 43.6045, 1.4442, 150.0],
            'lyon-to-marseille' => [45.7640, 4.8357, 200.0, 43.2965, 5.3698, 5.0],
        ];
    }
}
