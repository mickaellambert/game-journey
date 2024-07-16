<?php

namespace App\Service;

interface EntityFactoryInterface
{
    public function findOrCreate(string $value);
}