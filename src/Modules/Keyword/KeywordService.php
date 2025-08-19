<?php


namespace App\Modules\Keyword;

class KeywordService
{
    public function __construct(private KeywordRepository $keywordRepository) {}

    public function getAll()
    {
        return array_map(fn($k) => KeywordMapper::mapToDto($k), $this->keywordRepository->getAll());
    }
}
