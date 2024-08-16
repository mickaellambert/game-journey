<?php

namespace App\Entity;

use ApiPlatform\Metadata\Post;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\State\UserGameAddProcessor;
use ApiPlatform\Metadata\ApiResource;
use App\Repository\UserGameRepository;
use App\State\UserGameImportSteamProcessor;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserGameRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/collection',
            processor: UserGameAddProcessor::class,
            inputFormats: ['json' => ['application/json']],
            outputFormats: ['json' => ['application/json']],
            openapiContext: [
                'summary' => 'Add a game to the user\'s collection',
                'description' => 'Allows a user to add a specific game to their personal collection.',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'user_id' => [
                                        'type' => 'integer',
                                        'description' => 'The ID of the user adding the game.'
                                    ],
                                    'game_id' => [
                                        'type' => 'integer',
                                        'description' => 'The ID of the game to be added.'
                                    ],
                                    'status' => [
                                        'type' => 'string',
                                        'description' => 'The status of the game in the user\'s collection.',
                                        'enum' => UserGame::STATUSES
                                    ],
                                    'platform_id' => [
                                        'type' => 'integer',
                                        'description' => 'The ID of the platform on which the game is played.'
                                    ],
                                    'review' => [
                                        'type' => 'string',
                                        'description' => 'Optional review of the game by the user.',
                                        'maxLength' => UserGame::MAX_LENGTH_REVIEW
                                    ],
                                    'rating' => [
                                        'type' => 'integer',
                                        'description' => 'Optional rating of the game by the user (0 to 5).',
                                        'minimum' => 0,
                                        'maximum' => 5
                                    ],
                                ],
                                'required' => ['user_id', 'game_id', 'status', 'platform_id']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Game successfully added to the collection',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/UserGame'
                                ]
                            ]
                        ]
                    ],
                    '400' => [
                        'description' => 'Invalid input data',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string'],
                                        'code' => ['type' => 'integer'],
                                        'errors' => [
                                            'type' => 'object',
                                            'additionalProperties' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '404' => [
                        'description' => 'User or Platform not found',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string'],
                                        'code' => ['type' => 'integer'],
                                        'errors' => [
                                            'type' => 'object',
                                            'additionalProperties' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '409' => [
                        'description' => 'Game already exists in the user\'s collection',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string'],
                                        'code' => ['type' => 'integer'],
                                        'errors' => [
                                            'type' => 'object',
                                            'additionalProperties' => ['type' => 'string']
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ),
        new Post(
            uriTemplate: '/collection/import-steam',
            processor: UserGameImportSteamProcessor::class,
            inputFormats: ['json' => ['application/json']],
            outputFormats: ['json' => ['application/json']],
            openapiContext: [
                'summary' => 'Import Steam games into the user\'s collection',
                'description' => 'Allows a user to import their Steam library into their personal collection.',
                'requestBody' => [
                    'required' => true,
                    'content' => [
                        'application/json' => [
                            'schema' => [
                                'type' => 'object',
                                'properties' => [
                                    'user_id' => [
                                        'type' => 'integer',
                                        'description' => 'The ID of the user performing the import.'
                                    ],
                                    'steam_id' => [
                                        'type' => 'string',
                                        'description' => 'The Steam ID of the user.'
                                    ]
                                ],
                                'required' => ['user_id', 'steam_id']
                            ]
                        ]
                    ]
                ],
                'responses' => [
                    '201' => [
                        'description' => 'Games successfully imported',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string'],
                                        'code' => ['type' => 'integer'],
                                        'nbGamesAdded' => [
                                            'type' => 'integer',
                                            'description' => 'Number of games that have been imported.',
                                        ],
                                        'missingGames' => [
                                            'type' => 'array',
                                            'description' => 'List of games that could not be imported due to missing data.',
                                            'items' => [
                                                'type' => 'object',
                                                'properties' => [
                                                    'appid' => ['type' => 'integer'],
                                                    'name' => ['type' => 'string']
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '400' => [
                        'description' => 'Invalid input data',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string'],
                                        'code' => ['type' => 'integer'],
                                        'message' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ],
                    '404' => [
                        'description' => 'User not found or no games found for the provided Steam ID',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'status' => ['type' => 'string'],
                                        'code' => ['type' => 'integer'],
                                        'message' => ['type' => 'string']
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        )
    ],
    normalizationContext: ['groups' => ['usergame:read']],
    denormalizationContext: ['groups' => ['usergame:write']]
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
    #[Groups(['usergame:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'collection')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['usergame:read', 'usergame:write'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'collectors')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['usergame:read', 'usergame:write'])]
    private ?Game $game = null;

    #[ORM\ManyToOne(targetEntity: Platform::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['usergame:read', 'usergame:write'])]
    private Platform $platform;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: UserGame::STATUSES)]
    #[Groups(['usergame:read', 'usergame:write'])]
    private ?string $status = self::STATUS_NOT_STARTED;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Assert\Length(max: self::MAX_LENGTH_REVIEW)]
    #[Groups(['usergame:read', 'usergame:write'])]
    private ?string $review = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(min: 0, max: 5)]
    #[Groups(['usergame:read', 'usergame:write'])]
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
