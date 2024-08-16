<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class GameAlreadyInCollectionException extends Exception
{
    public function __construct()
    {
        parent::__construct('User already has this game in their collection', Response::HTTP_CONFLICT);
    }
}