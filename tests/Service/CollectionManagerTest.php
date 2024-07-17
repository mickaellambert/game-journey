<?php

namespace App\Tests\Service;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Repository\GenreRepository;
use App\Repository\ModeRepository;
use App\Service\ApiErrorHandler;
use App\Service\CollectionManager;
use App\Service\IgdbApiService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class CollectionManagerTest extends TestCase
{
    private $igdbApiService;
    private $entityManager;
    private $validator;
    private $apiErrorHandler;
    private $gameRepository;
    private $genreRepository;
    private $modeRepository;
    private $collectionManager;

    protected function setUp(): void
    {
        $this->igdbApiService = $this->createMock(IgdbApiService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->validator = $this->createMock(ValidatorInterface::class);
        $this->apiErrorHandler = $this->createMock(ApiErrorHandler::class);
        $this->gameRepository = $this->createMock(GameRepository::class);
        $this->genreRepository = $this->createMock(GenreRepository::class);
        $this->modeRepository = $this->createMock(ModeRepository::class);

        $this->collectionManager = new CollectionManager(
            $this->igdbApiService,
            $this->entityManager,
            $this->validator,
            $this->apiErrorHandler,
            $this->gameRepository,
            $this->genreRepository,
            $this->modeRepository
        );
    }

    public function testAddGameValidData()
    {
        $data = [
            'game_id' => 1,
            'platform' => 'PC',
            'status' => Game::STATUS_NOT_STARTED
        ];

        $gameData = [
            'name' => 'Test Game',
            'platforms' => [['name' => 'PC']],
            'summary' => 'Test Summary',
            'cover' => ['url' => 'http://test.com/cover.jpg'],
            'involved_companies' => [
                ['company' => ['name' => 'Test Developer'], 'developer' => true, 'publisher' => true]
            ],
            'first_release_date' => 1609459200,
            'genres' => [['name' => 'Action']],
            'game_modes' => [['name' => 'Single player']]
        ];

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->igdbApiService->method('selectGameById')->willReturn($gameData);
        $this->gameRepository->method('findOneBy')->willReturn(null);
        $this->genreRepository->method('findOrCreate')->willReturn(new \App\Entity\Genre());
        $this->modeRepository->method('findOrCreate')->willReturn(new \App\Entity\Mode());

        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Game::class));
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->collectionManager->addGame($data);

        $this->assertEquals(Response::HTTP_CREATED, $result['status']);
    }

    public function testAddGameInvalidData()
    {
        $data = [];

        $violations = new ConstraintViolationList([
            new ConstraintViolation(
                'This value should not be blank.',
                '',
                [],
                '',
                'game_id',
                ''
            )
        ]);
        $this->validator->method('validate')->willReturn($violations);
        $this->apiErrorHandler->method('handle')->willReturn(['game_id' => 'This value should not be blank.']);

        $result = $this->collectionManager->addGame($data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result['status']);
        $this->assertArrayHasKey('game_id', $result['errors']);
    }

    public function testAddGameAlreadyExists()
    {
        $data = [
            'game_id' => 1,
            'platform' => 'PC',
            'status' => Game::STATUS_NOT_STARTED
        ];

        $gameData = [
            'name' => 'Test Game',
            'platforms' => [['name' => 'PC']],
            'summary' => 'Test Summary',
            'cover' => ['url' => 'http://test.com/cover.jpg'],
            'involved_companies' => [
                ['company' => ['name' => 'Test Developer'], 'developer' => true, 'publisher' => true]
            ],
            'first_release_date' => 1609459200,
            'genres' => [['name' => 'Action']],
            'game_modes' => [['name' => 'Single player']]
        ];

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->igdbApiService->method('selectGameById')->willReturn($gameData);
        $this->gameRepository->method('findOneBy')->willReturn(new Game());

        $result = $this->collectionManager->addGame($data);

        $this->assertEquals(Response::HTTP_CONFLICT, $result['status']);
        $this->assertEquals('Game already exists in the collection', $result['errors']['game']);
    }

    public function testAddGamePlatformNotAvailable()
    {
        $data = [
            'game_id' => 1,
            'platform' => 'Xbox',
            'status' => Game::STATUS_NOT_STARTED
        ];

        $gameData = [
            'name' => 'Test Game',
            'platforms' => [['name' => 'PC']],
            'summary' => 'Test Summary',
            'cover' => ['url' => 'http://test.com/cover.jpg'],
            'involved_companies' => [
                ['company' => ['name' => 'Test Developer'], 'developer' => true, 'publisher' => true]
            ],
            'first_release_date' => 1609459200,
            'genres' => [['name' => 'Action']],
            'game_modes' => [['name' => 'Single player']]
        ];

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->igdbApiService->method('selectGameById')->willReturn($gameData);
        $this->gameRepository->method('findOneBy')->willReturn(null);

        $result = $this->collectionManager->addGame($data);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $result['status']);
        $this->assertEquals('Game not available on specified platform', $result['errors']['platform']);
    }

    public function testAddGameNotFound()
    {
        $data = [
            'game_id' => 1,
            'platform' => 'PC',
            'status' => Game::STATUS_NOT_STARTED
        ];

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->igdbApiService->method('selectGameById')->willReturn(null);

        $result = $this->collectionManager->addGame($data);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $result['status']);
        $this->assertEquals('Game not found', $result['errors']['game']);
    }

    public function testAddGameWithGenresAndModes()
    {
        $data = [
            'game_id' => 1,
            'platform' => 'PC',
            'status' => Game::STATUS_NOT_STARTED
        ];

        $gameData = [
            'name' => 'Test Game',
            'platforms' => [['name' => 'PC']],
            'summary' => 'Test Summary',
            'cover' => ['url' => 'http://test.com/cover.jpg'],
            'involved_companies' => [
                ['company' => ['name' => 'Test Developer'], 'developer' => true, 'publisher' => true]
            ],
            'first_release_date' => 1609459200,
            'genres' => [['name' => 'Action']],
            'game_modes' => [['name' => 'Single player']]
        ];

        $this->validator->method('validate')->willReturn(new ConstraintViolationList());
        $this->igdbApiService->method('selectGameById')->willReturn($gameData);
        $this->gameRepository->method('findOneBy')->willReturn(null);
        $this->genreRepository->method('findOrCreate')->willReturn(new \App\Entity\Genre());
        $this->modeRepository->method('findOrCreate')->willReturn(new \App\Entity\Mode());

        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Game::class));
        $this->entityManager->expects($this->once())->method('flush');

        $result = $this->collectionManager->addGame($data);

        $this->assertEquals(Response::HTTP_CREATED, $result['status']);
    }
}
