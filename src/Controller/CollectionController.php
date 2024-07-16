<?php

namespace App\Controller;

use App\Service\CollectionManager;
use App\Service\IgdbApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/collection')]
class CollectionController extends AbstractController
{
    public function __construct(
        private IgdbApiService $igdbApiService, 
        private CollectionManager $collectionManager)
    {}

    #[Route('', name: 'collection.add', methods: ['POST'])]
    public function add(Request $request): JsonResponse
    {
        $result = $this->collectionManager->addGame(json_decode($request->getContent(), true));

        return $this->json($result['data'], $result['status']);
    }
}