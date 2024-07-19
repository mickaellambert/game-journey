<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Repository\UserGameRepository;
use App\State\UserGameAddProcessor;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserGameRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/collection',
            processor: UserGameAddProcessor::class,
            openapiContext: [
                'summary' => 'Add a new game to the collection',
                'description' => 'This endpoint allows you to add a new game to your personal collection. It checks if the game exists in the database, if not, it creates a new one.',
                'requestBody' => [
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'user_id' => ['type' => 'integer', 'description' => 'ID of the user adding the game.'],
                                    'game_id' => ['type' => 'integer', 'description' => 'ID of the game. If provided, it must be the last data to provide.'],
                                    'status' => [
                                        'type' => 'string',
                                        'description' => 'Status of the game in the collection.',
                                        'enum' => UserGame::STATUSES
                                    ],
                                    'platform' => ['type' => 'string', 'description' => 'Platform for the user\'s game.'],
                                    'name' => ['type' => 'string', 'description' => 'Name of the game. Required if game_id is not provided.'],
                                    'description' => ['type' => 'string', 'description' => 'Description of the game. Required if game_id is not provided.'],
                                    'cover' => ['type' => 'string', 'description' => 'Cover image URL. Required if game_id is not provided.'],
                                    'released_at' => ['type' => 'string', 'format' => 'date-time', 'description' => 'Release date of the game. Required if game_id is not provided.'],
                                    'genres' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                        'description' => 'Genres of the game. Required if game_id is not provided.'
                                    ],
                                    'modes' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                        'description' => 'Game modes available. Required if game_id is not provided.'
                                    ],
                                    'platforms' => [
                                        'type' => 'array',
                                        'items' => ['type' => 'string'],
                                        'description' => 'Platforms the game is available on. Required if game_id is not provided.'
                                    ],
                                    'developer' => ['type' => 'string', 'description' => 'Developer of the game. Required if game_id is not provided.'],
                                    'publisher' => ['type' => 'string', 'description' => 'Publisher of the game. Required if game_id is not provided.']
                                ],
                                'required' => ['user_id', 'status', 'platform'],
                                'dependencies' => [
                                    'game_id' => [
                                        'not' => [
                                            'required' => [
                                                'name', 'description', 'cover', 'released_at', 'genres', 'modes', 'platforms', 'developer', 'publisher'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Game successfully added to the collection'
                    ],
                    '400' => [
                        'description' => 'Invalid input data'
                    ],
                    '404' => [
                        'description' => 'User not found'
                    ]
                ]
            ],
            status: 201
        )
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']]
)]
class UserGame
{
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_WISHLIST = 'wishlist';
    public const STATUS_ABANDONED = 'abandoned';
    public const STATUSES = [
        self::STATUS_NOT_STARTED,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_WISHLIST,
        self::STATUS_ABANDONED
    ];

    public const MAX_LENGTH_REVIEW = 5000;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collection')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'collectors')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Game $game = null;

    #[ORM\ManyToOne(targetEntity: Platform::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Platform $platform;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: UserGame::STATUSES)]
    private ?string $status = self::STATUS_NOT_STARTED;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: self::MAX_LENGTH_REVIEW)]
    private ?string $review = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 5)]
    private ?int $rating = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getGame(): ?Game
    {
        return $this->game;
    }

    public function setGame(?Game $game): static
    {
        $this->game = $game;

        return $this;
    }

    public function getPlatform(): Platform
    {
        return $this->platform;
    }

    public function setPlatform(Platform $platform): self
    {
        $this->platform = $platform;
        return $this;
    }
    
    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getReview(): ?string
    {
        return $this->review;
    }

    public function setReview(?string $review): static
    {
        $this->review = $review;

        return $this;
    }

    public function getRating(): ?int
    {
        return $this->rating;
    }

    public function setRating(?int $rating): static
    {
        $this->rating = $rating;

        return $this;
    }
}
