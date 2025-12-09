<?php

declare(strict_types=1);

namespace App\Infra\Symfony\Console\Command;

use App\App\Dto\ParkVehicleInputDTO as AppParkVehicleCommand;
use App\App\Handler\ParkVehicleHandler;
use App\Infra\Repository\FleetRepository;
use App\Infra\Db\PdoFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fleet:localize-vehicle')]
final class LocalizeVehicleCommand extends Command
{
    public function __construct(private readonly PdoFactory $pdoFactory)
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setDescription('Set the current location of a vehicle in a fleet')
            ->addArgument('fleetId', InputArgument::REQUIRED, 'Fleet identifier')
            ->addArgument('vehiclePlateNumber', InputArgument::REQUIRED, 'Vehicle plate number')
            ->addArgument('lat', InputArgument::REQUIRED, 'Latitude')
            ->addArgument('lng', InputArgument::REQUIRED, 'Longitude')
            ->addArgument('alt', InputArgument::OPTIONAL, 'Altitude');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fleetId = (string) $input->getArgument('fleetId');
        $plate = (string) $input->getArgument('vehiclePlateNumber');
        $lat = (float) $input->getArgument('lat');
        $lng = (float) $input->getArgument('lng');
        $altArg = $input->getArgument('alt');
        $alt = null !== $altArg ? (float) $altArg : null;

        $pdo = $this->pdoFactory->get();
        $repo = new FleetRepository($pdo);

        $handler = new ParkVehicleHandler($repo);
        $handler(new AppParkVehicleCommand($fleetId, $plate, $lat, $lng, $alt));

        $output->writeln('OK');

        return Command::SUCCESS;
    }
}
