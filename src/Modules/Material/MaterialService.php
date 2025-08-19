<?php

namespace App\Modules\Material;

class MaterialService
{
    public function __construct(private MaterialRepository $materialRepository) {}

    public function getAll(){
        $materials = $this->materialRepository->getAll();
        return array_map(fn($m) => MaterialMapper::mapToMaterialDto($m), $materials);
    }
}
