<?php

namespace App\Services\Extractors;

interface ExtractorInterface
{
    public function extract(array $parameters): array;
}
