<?php

namespace App\State;

use App\Entity\Client;
use App\Entity\Platform;
use App\Entity\UserGame;
use App\Exception\InvalidDataException;
use App\Service\Api\SteamApi;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use ApiPlatform\Metadata\Operation;
use App\Repository\ClientRepository;
use App\Repository\PlatformRepository;
use App\Repository\GameClientRepository;
use App\Service\Manager\UserGameManager;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Game;
use App\Entity\User;
use App\Exception\NotFoundException;
use App\Repository\UserGameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;

class UserGameImportSteamProcessor implements ProcessorInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserRepository $userRepository,
        private GameRepository $gameRepository,
        private PlatformRepository $platformRepository,
        private ClientRepository $clientRepository,
        private GameClientRepository $gameClientRepository,
        private UserGameManager $userGameManager,
        private UserGameRepository $userGameRepository,
        private SteamApi $steamApi,
    ) {}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        ini_set('memory_limit', '-1');

        $request = $context['request'] ?? null;

        if (!$request instanceof Request) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid request.');
        }

        $requestData = json_decode($request->getContent(), true);

        $userId = $requestData['user_id'] ?? null;
        $steamId = $requestData['steam_id'] ?? null;

        if (!$userId || !$steamId) {
            $exception = new InvalidDataException('User ID or Steam ID missing');
            throw new HttpException($exception->getCode(), $exception->getMessage());
        }

        $user = $this->userRepository->find($userId);

        if (!$user) {
            $exception = new UserNotFoundException(User::class);
            throw new HttpException($exception->getCode(), $exception->getMessage());
        }

        $steamGames = $this->steamApi->findAllGames($steamId);

        if (!$steamGames) {
            $exception = new NotFoundException(Game::class);
            throw new HttpException($exception->getCode(), $exception->getMessage());
        }

        $client = $this->clientRepository->find(Client::STEAM); 

        $missingGames = [];
        $nbGamesAdded = 0;
        
        foreach ($steamGames as $steamGame) {
            $gameClient = $this->gameClientRepository->findOneBy([
                'client' => $client,
                'reference' => $steamGame['appid']
            ]);

            $game = $gameClient ? $gameClient->getGame() : $this->gameRepository->findBestMatchingByName($steamGame['name']);

            if (!$game) {
                $missingGames[] = $steamGame;
                continue;
            }

            $userGame = $this->userGameRepository->findOneBy([
                'user' => $user,
                'game' => $game
            ]);

            if (!$userGame) {
                $userGame = new UserGame();
                $userGame->setUser($user);
                $userGame->setGame($game);
                $userGame->setPlatform($this->platformRepository->find(Platform::PC));
                $userGame->setStatus(UserGame::STATUS_IN_PROGRESS);
    
                $this->entityManager->persist($userGame);
                $nbGamesAdded++;
            }
        }

        $this->entityManager->flush();

        return ['nbGamesAdded' => $nbGamesAdded, 'missingGames' => $missingGames];
    }
}
