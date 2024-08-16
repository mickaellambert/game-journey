<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UserGameDTO
{
    #[Assert\NotBlank(message: 'User ID is required')]
    private int $userId;

    #[Assert\NotBlank(message: 'Game is required')]
    private ?int $gameId = null;

    #[Assert\NotBlank(message: 'Platform is required')]
    private string $platformId;

    #[Assert\NotBlank(message: 'Status is required')]
    private string $status;

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    public function getGameId()
    {
        return $this->gameId;
    }

    public function setGameId($gameId)
    {
        $this->gameId = $gameId;

        return $this;
    }

    public function getPlatformId()
    {
        return $this->platformId;
    }

    public function setPlatformId($platformId)
    {
        $this->platformId = $platformId;

        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }
}
