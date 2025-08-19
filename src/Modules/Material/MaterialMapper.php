<?php

namespace App\Modules\Material;

use App\Modules\Material\DTOs\MaterialDto;

class MaterialMapper
{
    public static function mapToMaterialDto(object $row): MaterialDto
    {
        return new MaterialDto(
            id: (int) $row->id,
            ulgMark: $row->ulg_mark ?? null,
            brand: $row->brand ?? null,
            model: $row->model ?? null,
            type: $row->type ?? null,
            identificationCode: $row->identification_code ?? null,
            identificationNumber: $row->identification_number ?? null,
            serialNumber: $row->serial_number ?? null,
            distributorSerialNumber: $row->distributor_serial_number ?? null,
            domain: $row->domain ?? null,
            price: $row->price ?? null,
            purchaseOrder: $row->purchase_order ?? null,
            deploymentDate: $row->deployment_date ?? null,
            externNetidentityId: $row->extern_netidentity_id ?? null,
            isMobile: (bool) $row->is_mobile,
            comments: $row->comments ?? null,
        );
    }
}
