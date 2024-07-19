<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

#[Assert\Expression(
    expression: 'this.game_id != null or (this.name != null and this.description != null and this.cover != null and this.released_at != null and this.genres != null and this.modes != null and this.platforms != null and this.developer != null and this.publisher != null)',
    message: 'If game_id is not provided, all other game details are required.'
)]
class UserGameDTO
{
    #[Assert\NotBlank(message: 'User ID is required')]
    public int $user_id;

    #[Assert\NotBlank(message: 'Status is required')]
    public string $status;

    #[Assert\NotBlank(message: 'Platform is required')]
    public string $platform;

    public ?int $game_id = null;

    #[Assert\NotBlank(allowNull: true, message: 'Name is required if game_id is not provided')]
    public ?string $name = null;

    #[Assert\NotBlank(allowNull: true, message: 'Description is required if game_id is not provided')]
    public ?string $description = null;

    #[Assert\NotBlank(allowNull: true, message: 'Cover is required if game_id is not provided')]
    public ?string $cover = null;

    #[Assert\NotBlank(allowNull: true, message: 'Released at is required if game_id is not provided')]
    #[Assert\Date]
    public ?string $released_at = null;

    #[Assert\All([
        new Assert\NotBlank(message: 'Each genre is required if game_id is not provided')
    ])]
    public ?array $genres = null;

    #[Assert\All([
        new Assert\NotBlank(message: 'Each mode is required if game_id is not provided')
    ])]
    public ?array $modes = null;

    #[Assert\All([
        new Assert\NotBlank(message: 'Each platform is required if game_id is not provided')
    ])]
    public ?array $platforms = null;

    #[Assert\NotBlank(allowNull: true, message: 'Developer is required if game_id is not provided')]
    public ?string $developer = null;

    #[Assert\NotBlank(allowNull: true, message: 'Publisher is required if game_id is not provided')]
    public ?string $publisher = null;
}
