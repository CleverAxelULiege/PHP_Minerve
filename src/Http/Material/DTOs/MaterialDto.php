<?php

namespace App\Http\Material\DTOs;

use DateTime;

class MaterialDto
{
    public function __construct(
        public int $id,
        public ?string $ulgMark,
        public ?string $brand,
        public ?string $model,
        public ?string $type,
        public ?string $identificationCode,
        public ?string $identificationNumber,
        public ?string $serialNumber,
        public ?string $distributorSerialNumber,
        public ?string $domain,
        public ?string $price,
        public ?string $purchaseOrder,
        public ?string $deploymentDate,
        public ?string $externNetidentityId,
        public bool $isMobile,
        public ?string $comments,
    ) {}
}
