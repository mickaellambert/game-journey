<?php

namespace App\DTO;

use App\Entity\Game;
use Symfony\Component\Validator\Constraints as Assert;

class CollectionAddDTO
{
    #[Assert\NotBlank(message: 'Game ID is required')]
    #[Assert\Type('integer', message: 'Game ID must be an integer')]
    private $game_id;

    #[Assert\NotBlank(message: 'Platform is required')]
    #[Assert\Type('string', message: 'Platform must be a string')]
    private $platform;

    #[Assert\Type('string', message: 'Status must be a string')]
    #[Assert\Choice(choices: Game::STATUSES, message: 'Choose a valid status')]
    private $status = Game::STATUS_NOT_STARTED;

    public function getGameId(): ?int
    {
        return $this->game_id;
    }

    public function setGameId(?int $game_id): self
    {
        $this->game_id = $game_id;
        return $this;
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function setPlatform(?string $platform): self
    {
        $this->platform = $platform;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(?string $status): self
    {
        $this->status = $status;
        return $this;
    }
}
