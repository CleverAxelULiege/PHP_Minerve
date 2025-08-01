<?php

namespace App\User\DTOs;

class UserStaffDto
{
    public function __construct(public int $id, public string $ulgId, public string $name) {}
}
