<?php

namespace App\Http\Intervention\DTOs;

class AuthorMessageDto
{
    public function __construct(
        public ?int $id,
        public ?string $firstName,
        public ?string $lastName,
        public ?string $ulgId
    ) {}
}
