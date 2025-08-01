<?php

namespace App\User;

use App\User\DTOs\UserStaffDto;

class UserMapper
{
    public static function mapToUserStaffDto(object $data): UserStaffDto
    {
        $dto = new UserStaffDto($data->user_id, $data->user_ulg_id, $data->user_name);
        return $dto;
    }
}
