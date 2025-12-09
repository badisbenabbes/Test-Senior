<?php

declare(strict_types=1);

namespace App\Infra\Symfony\Console\Command;

use App\App\Dto\RegisterVehicleInputDTO as AppRegisterVehicleCommand;
use App\App\Handler\RegisterVehicleHandler;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fleet:register-vehicle')]
final class RegisterVehicleCommand extends Command
{
    public function __construct(private readonly RegisterVehicleHandler $handler)
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->setDescription('Register a vehicle in a fleet')
            ->addArgument('fleetId', InputArgument::REQUIRED, 'Fleet identifier')
            ->addArgument('vehiclePlateNumber', InputArgument::REQUIRED, 'Vehicle plate number');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $fleetId = (string)$input->getArgument('fleetId');
        $plate = (string)$input->getArgument('vehiclePlateNumber');

        ($this->handler)(new AppRegisterVehicleCommand($fleetId, $plate));

        $output->writeln('OK');

        return Command::SUCCESS;
    }
}
