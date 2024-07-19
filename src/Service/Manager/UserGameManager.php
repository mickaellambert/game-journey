<?php

namespace App\Service\Manager;

use App\Exception\GameNotFoundException;
use App\Exception\InvalidGameDataException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\DTO\UserGameDTO;
use App\Entity\UserGame;
use App\Repository\GameRepository;
use App\Repository\PlatformRepository;
use App\Repository\UserGameRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

class UserGameManager
{
    public function __construct(
        private EntityManagerInterface $entityManager, 
        private UserGameRepository $userGameRepository,
        private GameRepository $gameRepository,
        private UserRepository $userRepository,
        private PlatformRepository $platformRepository,
        private GameManager $gameManager,
        private ValidatorInterface $validator
        )
    {}

    public function add(UserGameDTO $dto): array
    {
        $errors = $this->validator->validate($dto);
        
        if (count($errors) > 0) {
            $errorMessages = [];
            
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }

            return [
                'status' => 'error',
                'code' => Response::HTTP_BAD_REQUEST,
                'errors' => $errorMessages
            ];
        }

        $user = $this->userRepository->find($dto->user_id);
        
        if (!$user) {
            return [
                'status' => 'error',
                'code' => Response::HTTP_NOT_FOUND,
                'errors' => ['user' => 'User not found']
            ];
        }

        $game = $this->gameManager->findOrCreate((array) $dto);

        if (!$game) {
            return [
                'status' => 'error',
                'code' => Response::HTTP_NOT_FOUND,
                'errors' => ['user' => 'User not found']
            ];
        }

        $userGame = $this->userGameRepository->findOneBy(['user' => $user, 'game' => $game]);

        if ($userGame) {
            return [
                'status' => 'error',
                'code' => Response::HTTP_CONFLICT,
                'errors' => ['game' => 'User already has this game in their collection']
            ];
        }

        $userGame = new UserGame();
        $userGame->setUser($user);
        $userGame->setGame($game);
        $userGame->setPlatform($this->platformRepository->findOrCreate(['name' => $dto->platform]));
        $userGame->setStatus($dto->status);

        $this->entityManager->persist($userGame);
        $this->entityManager->flush();

        return [
            'status' => 'success',
            'code' => Response::HTTP_CREATED,
        ];
    }
}
