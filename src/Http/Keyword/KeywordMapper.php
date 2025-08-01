<?php

namespace App\Http\Keyword;

use App\Http\Keyword\DTOs\KeywordDto;

class KeywordMapper
{
    public static function mapToDto(object $object)
    {
        $dto = new KeywordDto();
        $dto->id = $object->id;
        $dto->name = $object->name;

        return $dto;
    }
}
