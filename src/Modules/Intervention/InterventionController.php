<?php

namespace App\Modules\Intervention;

use App\Helpers\NumberHelper;
use App\Modules\Keyword\KeywordService;
use App\Modules\Material\MaterialService;
use App\Support\Query;
use App\Modules\User\UserService;
use App\Support\PaginatedResult;
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
        $resultsPerPage = Query::getParameter("results_per_page", 50);
        $paginatedResults = $this->interventionService->getPaginatedInterventions($page, $resultsPerPage);
        $pagesDisplay = PaginatedResult::getPageSelection($paginatedResults->perPage, $paginatedResults->total, $paginatedResults->currentPage);
        $udiStaff = $this->userService->getUDIStaff();
        $keywords = $this->keywordService->getAll();
        $interventionTypes = $this->interventionService->getInterventionTypes();
        $materials = $this->materialService->getAll();

        return $view->render(
            "intervention/index",
            [
                "paginatedResults" => $paginatedResults,
                "udiStaff" => $udiStaff,
                "materials" => $materials,
                "keywords" => $keywords,
                "interventionTypes" => $interventionTypes,
                "pagesDisplay" => $pagesDisplay
            ]
        );
    }

    public function interventionFileImages()
    {
        header("Content-Type: application/json");

        if (!isset($_FILES['files'])) {
            http_response_code(405);
            return json_encode(["msg" => "Requête invalide"], JSON_UNESCAPED_UNICODE);
            exit;
        }

        return json_encode($this->interventionService->interventionFileImages($_FILES["files"]), JSON_UNESCAPED_UNICODE);
    }

    public function apiShow($id)
    {
        header("Content-Type: application/json");

        if (!NumberHelper::isCastableToInt($id)) {
            return json_encode(null);
        }

        $intervention = $this->interventionService->getById($id);
        return json_encode($intervention, JSON_UNESCAPED_UNICODE);
    }
}
