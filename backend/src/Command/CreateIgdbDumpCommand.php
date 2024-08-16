<?php

namespace App\Command;

use App\Service\Api\IgdbApi;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;

class CreateIgdbDumpCommand extends Command
{
    protected static $defaultName = 'app:create-igdb-dump';
    private IgdbApi $igdbApi;

    public function __construct(IgdbApi $igdbApi)
    {
        parent::__construct();
        $this->igdbApi = $igdbApi;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Imports data from IGDB API')
            ->addArgument('endpoint', InputArgument::REQUIRED, 'The IGDB API endpoint to download data from (e.g., games)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '512M');

        $endpoint = $input->getArgument('endpoint');
        
        $io = new SymfonyStyle($input, $output);
        $io->title('Creating IGDB ' . ucfirst($endpoint) . ' Dump');

        $this->igdbApi->findAllByDump($endpoint);

        $io->success('Dump created successfully.');

        return Command::SUCCESS;
    }
}
