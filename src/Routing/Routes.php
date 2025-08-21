<?php

namespace App\Routing;

use App\Database\Database;
use App\Modules\Home\HomeController;
use App\Modules\Intervention\InterventionController;
use App\Modules\Intervention\InterventionRepository;
use App\Modules\Intervention\InterventionService;
use App\Modules\Keyword\KeywordRepository;
use App\Modules\Keyword\KeywordService;
use App\Modules\Material\MaterialRepository;
use App\Modules\Material\MaterialService;
use App\Routing\Router;
use App\Modules\User\UserController;
use App\Modules\User\UserRepository;
use App\Modules\User\UserService;

class Routes
{
    public static function register(Router $router)
    {
        $router->get("/", function () {
            $homeController = new HomeController();

            return $homeController->index();
        });

        $router->get('/intervention', function () {
            $database = new Database();

            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);

            $keywordRepository = new KeywordRepository($database);
            $keywordService = new KeywordService($keywordRepository);

            $materialRepository = new MaterialRepository($database);
            $materialService = new MaterialService($materialRepository);

            $interventionRepository = new InterventionRepository($database);
            $interventionService = new InterventionService($interventionRepository);
            $controller = new InterventionController($interventionService, $userService, $keywordService, $materialService);
            return $controller->index();
        });

        $router->get('/api/intervention/{id}', function ($params) {
            $id = $params["id"];

            $database = new Database();

            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);

            $keywordRepository = new KeywordRepository($database);
            $keywordService = new KeywordService($keywordRepository);

            $materialRepository = new MaterialRepository($database);
            $materialService = new MaterialService($materialRepository);

            $interventionRepository = new InterventionRepository($database);
            $interventionService = new InterventionService($interventionRepository);
            $controller = new InterventionController($interventionService, $userService, $keywordService, $materialService);
            return $controller->apiShow($id);
        });

        $router->post("/api/intervention_file_images", function () {
            $database = new Database();

            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);

            $keywordRepository = new KeywordRepository($database);
            $keywordService = new KeywordService($keywordRepository);

            $materialRepository = new MaterialRepository($database);
            $materialService = new MaterialService($materialRepository);

            $interventionRepository = new InterventionRepository($database);
            $interventionService = new InterventionService($interventionRepository);
            $controller = new InterventionController($interventionService, $userService, $keywordService, $materialService);

            return $controller->interventionFileImages();
        });


        $router->get("/api/user/staff", function () {
            $database = new Database();
            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);
            $controller = new UserController($userService);

            return $controller->getUDIStaff();
        });
        $router->get("/api/user", function () {
            $database = new Database();
            $userRepository = new UserRepository($database);
            $userService = new UserService($userRepository);
            $controller = new UserController($userService);

            return $controller->apiGetAll();
        });
    }

    public static function getActive(): string
    {
        return $GLOBALS["router"]->getBasePathActiveRoute();
    }
}
