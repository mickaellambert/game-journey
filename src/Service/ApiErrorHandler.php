<?php

namespace App\Service;

use Symfony\Component\Validator\ConstraintViolationListInterface;

class ApiErrorHandler
{
    public function handle(ConstraintViolationListInterface $errors): array
    {
        $messages = [];

        foreach ($errors as $error) {
            $messages[$error->getPropertyPath()] = $error->getMessage();
        }

        return $messages;
    }
}
