<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class NotFoundException extends Exception
{
    public function __construct(string $class)
    {
        $className = basename(str_replace('\\', '/', $class));

        parent::__construct("$className not found", Response::HTTP_NOT_FOUND);
    }
}
