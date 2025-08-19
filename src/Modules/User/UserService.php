<?php

namespace App\Modules\User;

class UserService
{
    public function __construct(private UserRepository $userRepository) {}

    public function getUDIStaff()
    {
        $udiStaff = $this->userRepository->getUDIStaff();
        return array_map(fn($u) => UserMapper::mapToUserStaffDto($u), $udiStaff);
    }

    public function getAll()
    {
        $users = $this->userRepository->getAll();
        return array_map(fn($u) => UserMapper::mapToUserDto($u), $users);
    }
}
