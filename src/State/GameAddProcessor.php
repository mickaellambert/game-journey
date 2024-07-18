<?php

namespace App\State;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Service\CollectionManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\SerializerInterface;

class GameAddProcessor implements ProcessorInterface
{
    private $collectionManager;
    private $serializer;

    public function __construct(CollectionManager $collectionManager, SerializerInterface $serializer)
    {
        $this->collectionManager = $collectionManager;
        $this->serializer = $serializer;
    }

    public function process($data, Operation $operation, array $uriVariables = [], array $context = []): void
    {
        $request = $context['request'] ?? null;

        if (!$request instanceof Request) {
            throw new HttpException(Response::HTTP_BAD_REQUEST, 'Invalid request.');
        }

        $data = json_decode($request->getContent(), true);

        $result = $this->collectionManager->addGame($data);

        if ($result['status'] !== Response::HTTP_CREATED) {
            throw new HttpException($result['status'], json_encode($result['errors']));
        }

        $response = new JsonResponse([], $result['status']);
        $response->send();
        exit;
    }
}