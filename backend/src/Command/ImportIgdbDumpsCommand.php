<?php

namespace App\Command;

use App\Entity\Game;
use App\Entity\GameClient;
use App\Repository\ClientRepository;
use App\Repository\GameRepository;
use App\Repository\GenreRepository;
use App\Repository\ModeRepository;
use App\Repository\PlatformRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ImportIgdbDumpsCommand extends Command
{
    protected static $defaultName = 'app:import-igdb-dumps';

    private EntityManagerInterface $entityManager;
    private GameRepository $gameRepository;
    private GenreRepository $genreRepository;
    private ModeRepository $modeRepository;
    private PlatformRepository $platformRepository;
    private ThemeRepository $themeRepository;
    private ClientRepository $clientRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GameRepository $gameRepository,
        GenreRepository $genreRepository,
        ModeRepository $modeRepository,
        PlatformRepository $platformRepository,
        ThemeRepository $themeRepository,
        ClientRepository $clientRepository,
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->gameRepository = $gameRepository;
        $this->genreRepository = $genreRepository;
        $this->modeRepository = $modeRepository;
        $this->platformRepository = $platformRepository;
        $this->themeRepository = $themeRepository;
        $this->clientRepository = $clientRepository;
    }

    protected function configure()
    {
        $this
            ->setName(self::$defaultName)
            ->setDescription('Import data from IGDB dumps');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', -1);

        $io = new SymfonyStyle($input, $output);

        $this->importModes();
        $io->success('Modes imported successfully.');

        $this->importGenres();
        $io->success('Genres imported successfully.');

        $this->importPlatforms();
        $io->success('Platforms imported successfully.');

        $this->importThemes();
        $io->success('Themes imported successfully.');

        $this->importGames();
        $io->success('Games imported successfully.');

        $this->importGamesClient($io); 
        $io->success('Games references imported successfully.');

        return Command::SUCCESS;
    }

    private function importModes()
    {
        $csv = Reader::createFromPath('dumps/1722664800_game_modes.csv', 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $this->modeRepository->findOrCreate([
                'igdbId' => $record['id'],
                'name' => $record['name']
            ]);
        }
    }

    private function importGenres()
    {
        $csv = Reader::createFromPath('dumps/1722664800_genres.csv', 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $this->genreRepository->findOrCreate([
                'igdbId' => $record['id'],
                'name' => $record['name']
            ]);
        }
    }

    private function importPlatforms()
    {
        $csv = Reader::createFromPath('dumps/1722664800_platforms.csv', 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $this->platformRepository->findOrCreate([
                'igdbId' => $record['id'],
                'name' => $record['name']
            ]);
        }
    }

    private function importThemes()
    {
        $csv = Reader::createFromPath('dumps/1722664800_themes.csv', 'r');
        $csv->setHeaderOffset(0);

        foreach ($csv->getRecords() as $record) {
            $this->themeRepository->findOrCreate([
                'igdbId' => $record['id'],
                'name' => $record['name']
            ]);
        }
    }

    private function importGames()
    {
        $csv = Reader::createFromPath('dumps/1722664800_games.csv', 'r');
        $csv->setHeaderOffset(0);

        $covers = $this->importCovers();
        $companies = $this->importCompanies();
        $involvedCompanies = $this->importInvolvedCompanies();

        foreach ($csv->getRecords() as $record) {
            try {
                $releasedAt = !empty($record['first_release_date']) ? new \DateTime($record['first_release_date']) : null;
            }
            catch (Exception $e) {
                $releasedAt = null;
            }

            $game = new Game();
            $game->setIgdbId($record['id']);
            $game->setName($record['name']);
            $game->setDescription($record['summary'] ?? null);
            $game->setReleasedAt($releasedAt);
            $game->setCover($covers[$record['cover']] ?? null);
            $game->setDeveloper($this->getCompanyName($record['involved_companies'], $involvedCompanies, $companies, 'developer'));
            $game->setPublisher($this->getCompanyName($record['involved_companies'], $involvedCompanies, $companies, 'publisher'));

            if (!empty($record['genres'])) {
                foreach (explode(',', $record['genres']) as $genreId) {
                    $genre = $this->genreRepository->findOneBy(['igdbId' => $genreId]);
                    if ($genre) {
                        $game->addGenre($genre);
                    }
                }
            }

            if (!empty($record['platforms'])) {
                foreach (explode(',', $record['platforms']) as $platformId) {
                    $platform = $this->platformRepository->findOneBy(['igdbId' => $platformId]);
                    if ($platform) {
                        $game->addPlatform($platform);
                    }
                }
            }

            if (!empty($record['game_modes'])) {
                foreach (explode(',', $record['game_modes']) as $modeId) {
                    $mode = $this->modeRepository->findOneBy(['igdbId' => $modeId]);
                    if ($mode) {
                        $game->addMode($mode);
                    }
                }
            }

            if (!empty($record['themes'])) {
                foreach (explode(',', $record['themes']) as $themeId) {
                    $theme = $this->themeRepository->findOneBy(['igdbId' => $themeId]);
                    if ($theme) {
                        $game->addTheme($theme);
                    }
                }
            }

            $this->entityManager->persist($game);
        }

        $this->entityManager->flush();
    }

    private function importGamesClient($io) 
    {
        $csv = Reader::createFromPath('dumps/1723183200_external_games.csv', 'r');
        $csv->setHeaderOffset(0);

        $categories = [
            1 => 'steam',
            3 => 'giantbomb',
            5 => 'gog',
            10 => 'youtube',
            11 => 'microsoft',
            13 => 'apple',
            14 => 'twitch',
            15 => 'android',
            20 => 'amazon_asin',
            22 => 'amazon_luna',
            23 => 'amazon_adg',
            26 => 'epic_game_store',
            28 => 'oculus',
            29 => 'utomik',
            30 => 'itch_io',
            31 => 'xbox_marketplace',
            32 => 'kartridge',
            36 => 'playstation_store_us',
            37 => 'focus_entertainment',
            54 => 'xbox_game_pass_ultimate_cloud',
            55 => 'gamejolt',
        ];

        $i = 1;

        foreach ($csv->getRecords() as $record) {
            $gameClient = new GameClient();

            $client = $this->clientRepository->findOneByName($categories[$record['category']]);
            $game = $this->gameRepository->findOneByIgdbId($record['game']);

            if ($game && $client) {
                $gameClient->setClient($client);
                $gameClient->setGame($game);
                $gameClient->setReference($record['uid']);
    
                $this->entityManager->persist($gameClient);
    
                $io->success($i . ' - Added game client for game ' . $game->getId() . ' and client ' . $client->getName());
            }
            else {
                $io->error($i . ' - No game or client found for game ' . $record['game'] . ' and client ' . $record['category']);
            }

            $i++;
        }
        
        $this->entityManager->flush();
    }

    private function importCovers()
    {
        $csv = Reader::createFromPath('dumps/1722664800_covers.csv', 'r');
        $csv->setHeaderOffset(0);

        $covers = [];
        
        foreach ($csv->getRecords() as $record) {
            $covers[$record['id']] = $record['image_id'];
        }

        return $covers;
    }

    private function importCompanies()
    {
        $csv = Reader::createFromPath('dumps/1722664800_companies.csv', 'r');
        $csv->setHeaderOffset(0);
        $companies = [];

        foreach ($csv->getRecords() as $record) {
            $companies[$record['id']] = $record['name'];
        }

        return $companies;
    }

    private function importInvolvedCompanies()
    {
        $csv = Reader::createFromPath('dumps/1722664800_involved_companies.csv', 'r');
        $csv->setHeaderOffset(0);

        $involvedCompanies = [];

        foreach ($csv->getRecords() as $record) {
            $involvedCompanies[$record['id']] = [
                'company' => $record['company'],
                'developer' => $record['developer'] === 't',
                'publisher' => $record['publisher'] === 'f'
            ];
        }

        return $involvedCompanies;
    }

    private function getCompanyName(string $involvedCompaniesString, array $involvedCompanies, array $companies, string $role): ?string
    {
        $companyIds = explode(',', trim($involvedCompaniesString, '{}'));

        foreach ($companyIds as $companyId) {
            if (isset($involvedCompanies[$companyId])) {
                $involvedCompany = $involvedCompanies[$companyId];
                if ($role === 'developer' && $involvedCompany['developer']) {
                    return $companies[$involvedCompany['company']] ?? null;
                }
                if ($role === 'publisher' && $involvedCompany['publisher']) {
                    return $companies[$involvedCompany['company']] ?? null;
                }
            }
        }

        return null;
    }
}
