<?php

namespace App\Modules\Keyword;

use App\Modules\Keyword\DTOs\KeywordDto;

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
