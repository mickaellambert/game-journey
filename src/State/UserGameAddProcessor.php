<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\DTO\UserGameDTO;
use App\Service\Manager\UserGameManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;

class UserGameAddProcessor implements ProcessorInterface
{
    private UserGameManager $userGameManager;
    private SerializerInterface $serializer;

    public function __construct(UserGameManager $userGameManager, SerializerInterface $serializer)
    {
        $this->userGameManager = $userGameManager;
        $this->serializer = $serializer;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $request = $context['request'] ?? null;

        if (!$request instanceof Request) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid request.');
        }

        $result = $this->userGameManager->add($this->serializer->deserialize($request->getContent(), UserGameDTO::class, 'json'));

        if ($result['status'] === 'error') {
            $response = new JsonResponse(['status' => $result['status'], 'code' => $result['code'], 'errors' => $result['errors']], $result['code']);
        } else {
            $response = new JsonResponse(['status' => $result['status'], 'code' => $result['code']], $result['code']);
        }

        $response->send();
        exit;
    }
}