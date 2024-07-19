<?php

namespace App\Tests\Service\Manager;

use App\DTO\UserGameDTO;
use App\Entity\Game;
use App\Entity\User;
use App\Entity\UserGame;
use App\Repository\GameRepository;
use App\Repository\PlatformRepository;
use App\Repository\UserGameRepository;
use App\Repository\UserRepository;
use App\Service\Manager\GameManager;
use App\Service\Manager\UserGameManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\HttpFoundation\Response;

class UserGameManagerTest extends TestCase
{
    private $entityManager;
    private $userGameRepository;
    private $gameRepository;
    private $userRepository;
    private $platformRepository;
    private $gameManager;
    private $validator;

    private $userGameManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->userGameRepository = $this->createMock(UserGameRepository::class);
        $this->gameRepository = $this->createMock(GameRepository::class);
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->platformRepository = $this->createMock(PlatformRepository::class);
        $this->gameManager = $this->createMock(GameManager::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->userGameManager = new UserGameManager(
            $this->entityManager,
            $this->userGameRepository,
            $this->gameRepository,
            $this->userRepository,
            $this->platformRepository,
            $this->gameManager,
            $this->validator
        );
    }

    public function testAddUserNotFound()
    {
        $userGameDTO = new UserGameDTO();
        $userGameDTO->user_id = 1;

        $this->userRepository->method('find')->willReturn(null);

        $result = $this->userGameManager->add($userGameDTO);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals(Response::HTTP_NOT_FOUND, $result['code']);
        $this->assertEquals(['user' => 'User not found'], $result['errors']);
    }

    public function testAddInvalidGameData()
    {
        $userGameDTO = new UserGameDTO();
        $userGameDTO->user_id = 1;

        $user = new User();
        $this->userRepository->method('find')->willReturn($user);
        
        $violation = new ConstraintViolation('Invalid data', '', [], '', 'field', null);
        $violations = new ConstraintViolationList([$violation]);
        $this->validator->method('validate')->willReturn($violations);

        $result = $this->userGameManager->add($userGameDTO);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result['code']);
        $this->assertEquals(['field' => 'Invalid data'], $result['errors']);
    }

    public function testAddGameAlreadyExistsForUser()
    {
        $userGameDTO = new UserGameDTO();
        $userGameDTO->user_id = 1;
        $userGameDTO->name = 'Test Game';
        $userGameDTO->released_at = '2023-01-01';
        $userGameDTO->platform = 'PC';
        $userGameDTO->status = 'in_progress';

        $user = new User();
        $game = new Game();
        $existingUserGame = new UserGame();

        $this->userRepository->method('find')->willReturn($user);
        $this->gameManager->method('findOrCreate')->willReturn($game);
        $this->userGameRepository->method('findOneBy')->willReturn($existingUserGame);

        $result = $this->userGameManager->add($userGameDTO);

        $this->assertEquals('error', $result['status']);
        $this->assertEquals(Response::HTTP_CONFLICT, $result['code']);
        $this->assertEquals(['game' => 'User already has this game in their collection'], $result['errors']);
    }

    public function testAddNewGame()
    {
        $userGameDTO = new UserGameDTO();
        $userGameDTO->user_id = 1;
        $userGameDTO->name = 'Test Game';
        $userGameDTO->released_at = '2023-01-01';
        $userGameDTO->platform = 'PC';
        $userGameDTO->status = 'in_progress';

        $user = new User();
        $game = new Game();

        $this->userRepository->method('find')->willReturn($user);
        $this->gameManager->method('findOrCreate')->willReturn($game);
        $this->userGameRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->userGameManager->add($userGameDTO);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(Response::HTTP_CREATED, $result['code']);
    }

    public function testAddGameById()
    {
        $userGameDTO = new UserGameDTO();
        $userGameDTO->user_id = 1;
        $userGameDTO->game_id = 1;
        $userGameDTO->platform = 'PC';
        $userGameDTO->status = 'in_progress';

        $user = new User();
        $game = new Game();

        $this->userRepository->method('find')->willReturn($user);
        $this->gameManager->method('findOrCreate')->willReturn($game);
        $this->userGameRepository->method('findOneBy')->willReturn(null);

        $this->entityManager->expects($this->once())->method('persist');
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->userGameManager->add($userGameDTO);

        $this->assertEquals('success', $result['status']);
        $this->assertEquals(Response::HTTP_CREATED, $result['code']);
    }
}
