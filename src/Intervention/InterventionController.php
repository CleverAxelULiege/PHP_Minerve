<?php

namespace App\Intervention;

use App\Database\Database;
use App\Support\Query;
use App\User\UserService;
use App\Views\ViewRender;

class InterventionController
{
    public function __construct(private InterventionService $interventionService, private UserService $userService) {}

    public function index()
    {
        $view = new ViewRender();
        $page = Query::getParameter("page", 1);
        $resultsPerPage = Query::getParameter("results_per_page", 25);
        $paginatedResults = $this->interventionService->getPaginatedInterventions($page, $resultsPerPage);
        $udiStaff = $this->userService->getUDIStaff();
        return $view->render("intervention/index", ["paginatedResults" => $paginatedResults, "udiStaff" => $udiStaff]);
    }
}
