<?php

namespace App\State;

use App\DTO\UserGameDTO;
use App\Entity\UserGame;
use ApiPlatform\Metadata\Operation;
use App\Service\Manager\UserGameManager;
use ApiPlatform\State\ProcessorInterface;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserGameAddProcessor implements ProcessorInterface
{
    private UserGameManager $userGameManager;
    private SerializerInterface $serializer;

    public function __construct(UserGameManager $userGameManager, SerializerInterface $serializer)
    {
        $this->userGameManager = $userGameManager;
        $this->serializer = $serializer;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): UserGame
    {
        $request = $context['request'] ?? null;

        if (!$request instanceof Request) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid request.');
        }

        try {
            $userGame = $this->userGameManager->add($this->serializer->deserialize($request->getContent(), UserGameDTO::class, 'json'));
        }
        catch (Exception $e) {
            throw new HttpException($e->getCode(), $e->getMessage());
        }

        return $userGame;
    }
}
