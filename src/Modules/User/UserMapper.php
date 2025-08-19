<?php

namespace App\Modules\User;

use App\Modules\User\DTOs\UserDto;
use App\Modules\User\DTOs\UserStaffDto;

class UserMapper
{
    public static function mapToUserStaffDto(object $data): UserStaffDto
    {
        $dto = new UserStaffDto($data->user_id, $data->user_ulg_id, $data->user_name, $data->user_surname);
        return $dto;
    }
    public static function mapToUserDto(object $data): UserDto
    {
        $dto = new UserDto();
        $dto->id = $data->id;
        $dto->ulgId = $data->ulg_id;
        $dto->lastname = $data->lastname;
        $dto->firstname = $data->firstname;
        $dto->surname = $data->surname;
        $dto->email = $data->email;
        $dto->phoneNumber = $data->phone_number;
        $dto->personalDirectory = $data->personal_directory;
        $dto->comments = $data->comments;
        $dto->isReachable = $data->reachable;
        return $dto;
    }
}
