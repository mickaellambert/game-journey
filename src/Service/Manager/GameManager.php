<?php

namespace App\Service\Manager;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\GenreRepository;
use App\Service\EntityFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ModeRepository;
use App\Repository\PlatformRepository;

class GameManager implements EntityFactoryInterface
{
    public function __construct(
        private GameRepository $gameRepository,
        private GenreRepository $genreRepository,
        private ModeRepository $modeRepository,
        private PlatformRepository $platformRepository,
        private EntityManagerInterface $entityManager
    ) {}

    public function findOrCreate(array $data): Game
    {
        if (!empty($data['game_id'])) {
            return $this->gameRepository->find($data['game_id']);
        }

        $game = $this->gameRepository->findOneByName($data['name']);
        
        if ($game) {
            return $game;
        }

        $game = new Game();
        $game->setName($data['name']);
        $game->setDescription($data['description'] ?? null);
        $game->setCover($data['cover'] ?? null);
        $game->setReleasedAt(\DateTime::createFromFormat('Y-m-d', $data['released_at']));

        foreach ($data['genres'] as $genre) {
            $game->addGenre($this->genreRepository->findOrCreate(['name' => $genre]));
        }

        foreach ($data['modes'] as $mode) {
            $game->addMode($this->modeRepository->findOrCreate(['name' => $mode]));
        }

        foreach ($data['platforms'] as $platform) {
            $game->addPlatform($this->platformRepository->findOrCreate(['name' => $platform]));
        }

        $game->setDeveloper($data['developer'] ?? null);
        $game->setPublisher($data['publisher'] ?? null);
        $game->setIgdbId($data['game_id'] ?? null);

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return $game;
    }
}
