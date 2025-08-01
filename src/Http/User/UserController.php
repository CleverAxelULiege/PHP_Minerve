<?php

namespace App\Http\User;

class UserController
{
    public function __construct(public UserService $userService) {}


    public function getUDIStaff()
    {
        header("Content-Type: application/json");
        return json_encode($this->userService->getUDIStaff(), JSON_UNESCAPED_UNICODE);
    }
}
