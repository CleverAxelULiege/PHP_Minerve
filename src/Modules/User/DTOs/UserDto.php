<?php

namespace App\Modules\User\DTOs;

class UserDto
{
    public ?int $id;
    public ?string $ulgId;
    public ?string $lastname;
    public ?string $firstname;
    public ?string $surname;
    public ?string $email;
    public ?string $phoneNumber;
    public ?string $personalDirectory;
    public ?string $comments;
    public bool $isReachable;
}
