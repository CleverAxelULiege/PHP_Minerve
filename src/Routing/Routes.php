<?php

namespace App\Routing;

use App\Database\Database;
use App\Http\Intervention\InterventionController;
use App\Http\Intervention\InterventionRepository;
use App\Http\Intervention\InterventionService;
use App\Http\Keyword\KeywordRepository;
use App\Http\Keyword\KeywordService;
use App\Routing\Router;
use App\Http\User\UserController;
use App\Http\User\UserRepository;
use App\Http\User\UserService;

class Routes
{
    public static function register(Router $router)
    {
        $router->get('/intervention', function () {
            $database = new Database();

            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);

            $keywordRepository = new KeywordRepository($database);
            $keywordService = new KeywordService($keywordRepository);

            $interventionRepository = new InterventionRepository($database);
            $interventionService = new InterventionService($interventionRepository);
            $controller = new InterventionController($interventionService, $userService, $keywordService);
            return $controller->index();
        });

        $router->get('/api/intervention/{id}', function ($params) {
            $id = $params["id"];

            $database = new Database();

            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);

            $keywordRepository = new KeywordRepository($database);
            $keywordService = new KeywordService($keywordRepository);

            $interventionRepository = new InterventionRepository($database);
            $interventionService = new InterventionService($interventionRepository);
            $controller = new InterventionController($interventionService, $userService, $keywordService);
            return $controller->apiShow($id);
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
