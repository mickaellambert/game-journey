<?php

namespace App\Exception;

use Exception;
use Symfony\Component\HttpFoundation\Response;

class InvalidDataException extends Exception
{
    public function __construct(string $message)
    {
        parent::__construct('Invalid input data : ' . $message, Response::HTTP_BAD_REQUEST);
    }
}
