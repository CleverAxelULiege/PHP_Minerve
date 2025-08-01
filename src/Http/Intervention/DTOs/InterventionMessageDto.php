<?php

namespace App\Http\Intervention\DTOs;

class InterventionMessageDto
{
    public function __construct(
        public int $id,
        public ?string $message,
        public bool $isPublic,
        public string $createdAt,
        public ?string $updatedAt,
        public AuthorMessageDto $author
    ) {}
}