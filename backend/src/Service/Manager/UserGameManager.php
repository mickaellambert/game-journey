<?php

namespace App\Service\Manager;

use App\Entity\Game;
use App\Entity\User;
use App\DTO\UserGameDTO;
use App\Entity\Platform;
use App\Entity\UserGame;
use App\Exception\GameAlreadyInCollectionException;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Exception\NotFoundException;
use App\Repository\PlatformRepository;
use App\Repository\UserGameRepository;
use App\Exception\InvalidDataException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserGameManager
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private UserGameRepository $userGameRepository,
        private GameRepository $gameRepository,
        private UserRepository $userRepository,
        private PlatformRepository $platformRepository,
        private ValidatorInterface $validator
        )
    {}

    public function add(UserGameDTO $dto): UserGame
    {
        $errors = $this->validator->validate($dto);
        
        if (count($errors) > 0) {
            throw new InvalidDataException($errors[0]->getMessage());
        }

        $user = $this->userRepository->find($dto->getUserId());
        
        if (!$user) {
            throw new NotFoundException(User::class);
        }

        $game = $this->gameRepository->find($dto->getGameId());

        if (!$game) {
            throw new NotFoundException(Game::class);
        }

        $platform = $this->platformRepository->find($dto->getPlatformId());
        
        if (!$platform || !$game->getPlatforms()->contains($platform)) {
            throw new NotFoundException(Platform::class);
        }

        $userGame = $this->userGameRepository->findOneBy(['user' => $user, 'game' => $game]);

        if ($userGame) {
            throw new GameAlreadyInCollectionException();
        }

        $userGame = new UserGame();
        $userGame->setUser($user);
        $userGame->setGame($game);
        $userGame->setPlatform($platform);
        $userGame->setStatus($dto->getStatus());

        $this->entityManager->persist($userGame);
        $this->entityManager->flush();

        return $userGame;
    }
}
