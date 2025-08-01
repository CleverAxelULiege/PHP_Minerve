<?php

namespace App\Http\Intervention\DTOs;



class InterventionTypeDto {
    public int $id;
    public string $name;
    /** @var InterventionSubtypeDto[] */
    public array $subTypes;
}