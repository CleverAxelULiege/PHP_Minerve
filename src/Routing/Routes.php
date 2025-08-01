<?php

namespace App\Routing;

use App\Database\Database;
use App\Intervention\InterventionController;
use App\Intervention\InterventionRepository;
use App\Intervention\InterventionService;
use App\Routing\Router;
use App\User\UserController;
use App\User\UserRepository;
use App\User\UserService;

class Routes
{
    public static function register(Router $router)
    {
        $router->get('/intervention', function () {
            $database = new Database();

            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);

            $interventionRepository = new InterventionRepository($database);
            $interventionService = new InterventionService($interventionRepository);
            $controller = new InterventionController($interventionService, $userService);
            return $controller->index();
        });
        $router->get('/api/intervention/{id}', function () {
            $database = new Database();

            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);

            $interventionRepository = new InterventionRepository($database);
            $interventionService = new InterventionService($interventionRepository);
            $controller = new InterventionController($interventionService, $userService);
            return $controller->index();
        });


        $router->get("/api/user/staff", function () {
            $database = new Database();
            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);
            $controller = new UserController($userService);

            return $controller->getUDIStaff();
        });
    }

    public static function getActive(): string
    {
        return $GLOBALS["router"]->getBasePathActiveRoute();
    }
}
