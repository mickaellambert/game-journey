<?php

namespace App\Service;

interface EntityFactoryInterface
{
    public function findOrCreate(array $data);
}