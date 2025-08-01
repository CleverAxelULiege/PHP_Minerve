<?php

namespace App\Http\Intervention;

use App\Support\PaginatedResult;
use App\Http\Intervention\DTOs\InterventionDto;
use App\Http\Intervention\DTOs\InterventionTypeDto;

class InterventionService
{

    public function __construct(private InterventionRepository $interventionRepository) {}

    public function getPaginatedInterventions(int $page, int $resultsPerPage)
    {
        $totalInterventions = $this->interventionRepository->getTotalInterventionsCount();

        $maxPages = (int) ceil($totalInterventions / $resultsPerPage);

        if ($page < 1) {
            $page = 1;
        } elseif ($page > $maxPages && $maxPages > 0) {
            $page = $maxPages;
        }

        /** @var InterventionDto[] */
        $mappedInterventions = array_map(fn($i) => InterventionMapper::mapToDto($i), $this->interventionRepository->getPaginatedInterventions($page, $resultsPerPage));

        $paginatedResult = new PaginatedResult($mappedInterventions, $page, $resultsPerPage, $totalInterventions);
        return $paginatedResult;
    }

    public function getInterventionTypes()
    {
        /** @var InterventionTypeDto[] */
        return array_map(fn($i) => InterventionMapper::fromRawToType($i), $this->interventionRepository->getInterventionTypes());
    }

    public function getById($interventionId)
    {
        $intervention = $this->interventionRepository->getInterventionWithDetails($interventionId);
        if ($intervention == null) {
            return null;
        }

        return InterventionMapper::mapToDto($intervention);
    }
}
