<?php

namespace App\Modules\User;

class UserController
{
    public function __construct(public UserService $userService) {}


    public function getUDIStaff()
    {
        header("Content-Type: application/json");
        return json_encode($this->userService->getUDIStaff(), JSON_UNESCAPED_UNICODE);
    }

    public function apiGetAll()
    {
        header("Content-Type: application/json");
        return json_encode($this->userService->getAll(), JSON_UNESCAPED_UNICODE);
    }
}
