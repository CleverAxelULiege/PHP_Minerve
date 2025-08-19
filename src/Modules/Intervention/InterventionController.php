<?php

namespace App\Modules\Intervention;

use App\Helpers\NumberHelper;
use App\Modules\Keyword\KeywordService;
use App\Modules\Material\MaterialService;
use App\Support\Query;
use App\Modules\User\UserService;
use App\Views\ViewRender;
use Exception;

class InterventionController
{
    public function __construct(
        private InterventionService $interventionService,
        private UserService $userService,
        private KeywordService $keywordService,
        private MaterialService $materialService
    ) {}

    public function index()
    {
        $view = new ViewRender();
        $page = Query::getParameter("page", 1);
        $resultsPerPage = Query::getParameter("results_per_page", 25);
        $paginatedResults = $this->interventionService->getPaginatedInterventions($page, $resultsPerPage);
        $udiStaff = $this->userService->getUDIStaff();
        $users = $this->userService->getAll();
        $keywords = $this->keywordService->getAll();
        $interventionTypes = $this->interventionService->getInterventionTypes();
        $materials = $this->materialService->getAll();

        return $view->render(
            "intervention/index",
            [
                "paginatedResults" => $paginatedResults,
                "udiStaff" => $udiStaff,
                "users" => $users,
                "materials" => $materials,
                "keywords" => $keywords,
                "interventionTypes" => $interventionTypes,
            ]
        );
    }

    public function apiShow($id)
    {
        header("Content-Type: application/json");
        
        if(!NumberHelper::isCastableToInt($id)) {
            return json_encode(null);
        }

        $intervention = $this->interventionService->getById($id);
        return json_encode($intervention, JSON_UNESCAPED_UNICODE);
    }
}
