<?php

namespace App\Service;

use App\DTO\CollectionAddDTO;
use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\GenreRepository;
use App\Repository\ModeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CollectionManager
{
    public function __construct(
        private IgdbApiService $igdbApiService, 
        private EntityManagerInterface $entityManager, 
        private ValidatorInterface $validator,
        private ApiErrorHandler $apiErrorHandler, 
        private GameRepository $gameRepository,
        private GenreRepository $genreRepository,
        private ModeRepository $modeRepository)
    {}

    public function addGame(array $data): array
    {
        $dto = new CollectionAddDTO();
        $dto->setGameId($data['game_id'] ?? null);
        $dto->setPlatform($data['platform'] ?? null);
        $dto->setStatus($data['status'] ?? Game::STATUS_NOT_STARTED);

        $errors = $this->validator->validate($dto);

        if (count($errors) > 0) {
            return [
                'status' => Response::HTTP_BAD_REQUEST, 
                'data' => $this->apiErrorHandler->handle($this->validator->validate($dto))
            ];
        }

        $gameData = $this->igdbApiService->selectGameById($dto->getGameId());

        if (empty($gameData)) {
            return [
                'status' => Response::HTTP_NOT_FOUND, 
                'data' => [
                    'game' => 'Game not found'
                ]
            ];
        }

        if (!in_array($dto->getPlatform(), array_column($gameData['platforms'], 'name'))) {
            return [
                'status' => Response::HTTP_BAD_REQUEST, 
                'data' => [
                    'platform' => 'Game not available on specified platform'
                ]
            ];
        }

        $game = $this->gameRepository->findOneBy([
            'name' => $gameData['name'], 
            'platform' => $dto->getPlatform()
        ]);

        if ($game) {
            return [
                'status' => Response::HTTP_CONFLICT, 'data' => [
                    'game' => 'Game already exists in the collection'
                ]
            ];
        }

        $game = new Game();
        $game->setName($gameData['name']);
        $game->setPlatform($dto->getPlatform());
        $game->setStatus($dto->getStatus());
        $game->setDescription($gameData['summary'] ?? null);
        $game->setCover($gameData['cover']['url'] ?? null);
        $game->setReleasedAt((new \DateTime())->setTimestamp($gameData['first_release_date']));

        foreach ($gameData['genres'] as $genre) {
            $game->addGenre($this->genreRepository->findOrCreate($genre['name']));
        }

        foreach ($gameData['game_modes'] as $mode) {
            $game->addMode($this->modeRepository->findOrCreate($mode['name']));
        }

        foreach ($gameData['involved_companies'] as $company) {
            if (isset($company['developer']) && $company['developer'] === true) {
                $game->setDeveloper($company['company']['name'] ?? null);
            }
            if (isset($company['publisher']) && $company['publisher'] === true) {
                $game->setPublisher($company['company']['name'] ?? null);
            }
        }

        $this->entityManager->persist($game);
        $this->entityManager->flush();

        return [
            'status' => Response::HTTP_CREATED, 
            'data' => [
                'message' => 'Game added successfully'
            ]
        ];
    }
}
