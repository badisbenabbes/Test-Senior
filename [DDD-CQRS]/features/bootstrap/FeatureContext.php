<?php

declare(strict_types=1);

use App\App\Calculator;
use App\App\Dto\CreateFleetInputDTO;
use App\App\Dto\ParkVehicleInputDTO;
use App\App\Dto\RegisterVehicleInputDTO;
use App\App\Handler\CreateFleetHandler;
use App\App\Handler\ParkVehicleHandler;
use App\App\Handler\RegisterVehicleHandler;
use App\Domain\Exceptions\VehicleAlreadyInFleetException;
use App\Domain\Exceptions\VehicleAlreadyParkedInThisLocation;
use App\Domain\Fleet\FleetId;
use App\Domain\Vehicle\VehicleLocation;
use App\Domain\Vehicle\VehiclePlateNumber;
use App\Infra\Repository\FleetRepository;
use Behat\Behat\Context\Context;

final class FeatureContext implements Context
{
    private FleetRepository $fleetRepository;
    private PDO $pdo;
    private CreateFleetHandler $createFleet;
    private RegisterVehicleHandler $registerVehicle;
    private ParkVehicleHandler $parkVehicle;

    private ?string $fleetId = null;
    private ?string $otherFleetId = null;
    private ?string $plate = null;
    private ?float $lat = null;
    private ?float $lng = null;
    private ?float $alt = null;
    private ?Throwable $lastException = null;
    /**
     * @var array<string, mixed>
     */
    private array $vars = [];

    public function __construct()
    {
        $this->pdo = self::createPdo();
        $this->ensureSchema($this->pdo);

        $this->fleetRepository = new FleetRepository($this->pdo);
        $this->createFleet = new CreateFleetHandler($this->fleetRepository);
        $this->registerVehicle = new RegisterVehicleHandler($this->fleetRepository);
        $this->parkVehicle = new ParkVehicleHandler($this->fleetRepository);
    }

    /**
     * @BeforeScenario
     */
    public function resetDatabase(): void
    {
        // Keep scenarios isolated
        $this->pdo->exec('TRUNCATE TABLE fleet_vehicle, fleet RESTART IDENTITY');
    }

    /**
     * @Given my fleet
     */
    public function myFleet(): void
    {
        $this->fleetId = (string) ($this->createFleet)(new CreateFleetInputDTO('user-1'));
    }

    /**
     * @Given the fleet of another user
     */
    public function theFleetOfAnotherUser(): void
    {
        $this->otherFleetId = (string) ($this->createFleet)(new CreateFleetInputDTO('user-2'));
    }

    /**
     * @Given a vehicle
     */
    public function aVehicle(): void
    {
        $this->plate = 'ABC-123';
    }

    /**
     * @Given I have registered this vehicle into my fleet
     */
    public function iHaveRegisteredThisVehicleIntoMyFleet(): void
    {
        ($this->registerVehicle)(new RegisterVehicleInputDTO($this->fleetId, $this->plate));
    }

    /**
     * @Given this vehicle has been registered into the other user's fleet
     */
    public function thisVehicleHasBeenRegisteredIntoTheOtherUsersFleet(): void
    {
        ($this->registerVehicle)(new RegisterVehicleInputDTO($this->otherFleetId, $this->plate));
    }

    /**
     * @When I register this vehicle into my fleet
     */
    public function iRegisterThisVehicleIntoMyFleet(): void
    {
        $this->lastException = null;
        try {
            ($this->registerVehicle)(new RegisterVehicleInputDTO($this->fleetId, $this->plate));
        } catch (Throwable $exception) {
            $this->lastException = $exception;
        }
    }

    /**
     * @Then this vehicle should be part of my vehicle fleet
     */
    public function thisVehicleShouldBePartOfMyVehicleFleet(): void
    {
        $fleet = $this->fleetRepository->get(new FleetId($this->fleetId));

        if (!$fleet?->hasVehicle(new VehiclePlateNumber($this->plate))) {
            throw new RuntimeException('Vehicle is not part of the fleet');
        }
    }

    /**
     * @Then I should be informed this vehicle has already been registered into my fleet
     */
    public function iShouldBeInformedVehicleAlreadyRegistered(): void
    {
        $this->assertLastException(VehicleAlreadyInFleetException::class);
    }

    /**
     * @Given a location
     */
    public function aLocation(): void
    {
        $this->lat = 48.8566;
        $this->lng = 2.3522;
        $this->alt = 35.0;
    }

    /**
     * @Given my vehicle has been parked in this location
     */
    public function myVehicleHasBeenParkedIntoThisLocation(): void
    {
        ($this->parkVehicle)(new ParkVehicleInputDTO($this->fleetId, $this->plate, $this->lat, $this->lng, $this->alt));
    }

    /** @When I park my vehicle at this location */
    public function iParkMyVehicleAtThisLocation(): void
    {
        $this->lastException = null;
        try {
            ($this->parkVehicle)(new ParkVehicleInputDTO($this->fleetId, $this->plate, $this->lat, $this->lng, $this->alt));
        } catch (Throwable $e) {
            $this->lastException = $e;
        }
    }

    /**
     * @When I try to register this vehicle into my fleet
     */
    public function iTryToRegisterThisVehicleIntoMyFleet(): void
    {
        $this->iRegisterThisVehicleIntoMyFleet();
    }

    /**
     * @When I try to park my vehicle at this location
     */
    public function iTryToParkMyVehicleAtThisLocation(): void
    {
        $this->iParkMyVehicleAtThisLocation();
    }

    /**
     * @Then the known location of my vehicle should verify this location
     */
    public function theKnownLocationOfMyVehicleShouldVerifyThisLocation(): void
    {
        $fleet = $this->fleetRepository->get(new FleetId($this->fleetId));
        $loc = $fleet?->getVehicleLocation(new VehiclePlateNumber($this->plate));

        $expected = new VehicleLocation($this->lat, $this->lng, $this->alt);

        if ($loc === null || !$loc->equals($expected)) {
            throw new RuntimeException('Location mismatch');
        }
    }

    /**
     * @Then I should be informed that my vehicle is already parked at this location
     */
    public function iShouldBeInformedVehicleAlreadyParked(): void
    {
        $this->assertLastException(VehicleAlreadyParkedInThisLocation::class);
    }

    /**
     * @When I multiply :x by :y into :var
     */
    public function iMultiply(int $a, int $b, string $var): void
    {
        $calculator = new Calculator();
        $this->$var = $calculator->multiply($a, $b);
    }

    /**
     * @Then :var should be equal to :value
     */
    public function aShouldBeEqualTo(string $var, int $value): void
    {
        if ($value !== $this->$var) {
            throw new RuntimeException(sprintf('%s is expected to be equal to %s, got %s', $var, $value, $this->$var));
        }
    }

    private static function createPdo(): PDO
    {
        $dsn = getenv('DB_DSN') ?: 'pgsql:host=localhost;port=5432;dbname=fleet';
        $user = getenv('DB_USER') ?: 'postgres';
        $pass = getenv('DB_PASSWORD') ?: 'admin';

        return new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    }

    private function ensureSchema( $pdo): void
    {
        $schemaPath = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'sql' . DIRECTORY_SEPARATOR . 'schema.sql';
        if (!is_readable($schemaPath)) {
            return;
        }

        $sql = (string)file_get_contents($schemaPath);

        foreach (array_filter(array_map('trim', preg_split('/;\s*\n/', $sql))) as $stmt) {
            if ('' !== $stmt) {
                $pdo->exec($stmt);
            }
        }
    }

    private function assertLastException(string $expectedClass): void
    {
        if (!$this->lastException instanceof $expectedClass) {
            $type = $this->lastException ? get_class($this->lastException) : 'none';
            throw new RuntimeException(sprintf('Expected %s, got %s', $expectedClass, $type));
        }
    }
}
