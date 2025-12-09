<?php

declare(strict_types=1);

namespace App\Infra\Symfony\Console\Command;

use App\App\Dto\CreateFleetInputDTO as AppCreateFleetCommand;
use App\App\Handler\CreateFleetHandler;
use App\Infra\Repository\FleetRepository;
use App\Infra\Db\PdoFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:fleet:create',
    description: 'Create a fleet for a given user id',
)]
final class CreateFleetCommand extends Command
{
    public function __construct(private readonly PdoFactory $pdoFactory)
    {
        parent::__construct();
    }
    protected function configure(): void
    {
        $this->addArgument('userId', InputArgument::REQUIRED, 'User identifier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $userId = (string) $input->getArgument('userId');

        $pdo = $this->pdoFactory->get();
        $repo = new FleetRepository($pdo);
        $handler = new CreateFleetHandler($repo);

        $fleetId = $handler(new AppCreateFleetCommand($userId));

        $output->writeln((string)$fleetId);

        return Command::SUCCESS;
    }
}
