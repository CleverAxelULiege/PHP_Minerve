<?php

namespace App\Modules\Intervention;

use App\Support\PaginatedResult;
use App\Modules\Intervention\DTOs\InterventionDto;
use App\Modules\Intervention\DTOs\InterventionTypeDto;

class InterventionService
{
    public const INTERVENTION_IMAGES_DIRECTORY = __DIR__ . "/../../../public/upload/intervention_images";

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
        $mappedInterventions = array_map(fn($i) => InterventionMapper::mapToInterventionDto($i), $this->interventionRepository->getPaginatedInterventions($page, $resultsPerPage));

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

        $interventionMapped =  InterventionMapper::mapToInterventionDto($intervention);
        $interventionMapped->messages =  array_map(fn($m) => InterventionMapper::mapToMessageDto($m), $this->interventionRepository->getInterventionMessages($interventionId));

        return $interventionMapped;
    }

    public function getInterventionMessages($interventionId)
    {
        $interventionMessages = $this->interventionRepository->getInterventionMessages($interventionId);
        return array_map(fn($m) => InterventionMapper::mapToMessageDto($m), $interventionMessages);
    }

    public function interventionFileImages(array $files)
    {

        $uploadedFiles = [];

        foreach ($files['name'] as $index => $originalName) {
            if ($files['error'][$index] !== UPLOAD_ERR_OK) {
                continue;
            }

            $tmpPath = $files['tmp_name'][$index];
            $mimeType = $files["type"][$index];

            $extMap = [
                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/gif'  => 'gif',
                'image/webp' => 'webp',
                'image/bmp'  => 'bmp',
                'image/svg+xml' => 'svg'
            ];

            $extension = $extMap[$mimeType] ?? null;

            if ($extension == null)
                continue;

            $uniqueName = uniqid('', true) . '_' . bin2hex(random_bytes(8));
            $newFilename = $uniqueName . '.' . strtolower($extension);

            $destination = InterventionService::INTERVENTION_IMAGES_DIRECTORY . '/' . $newFilename;
            if (move_uploaded_file($tmpPath, $destination)) {
                $uploadedFiles[] = [
                    'original_name' => $originalName,
                    'new_path' => "/upload/intervention_images/" . $newFilename
                ];
            }
        }


        return $uploadedFiles;
    }
}
