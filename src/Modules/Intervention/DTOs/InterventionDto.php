<?php

namespace App\Modules\Intervention\DTOs;

use App\Modules\Intervention\DTOs\HelperDto;
use App\Modules\Intervention\DTOs\KeywordDto;
use App\Modules\Intervention\DTOs\ServiceDto;

class InterventionDto
{
    public ?int $id;
    public ?string $requestDate;
    public ?string $updatedAt;
    public ?string $requestIp;
    public ?int $requesterUserId;
    public ?int $interventionTargetUserId;
    public ?int $lockedByUserId;
    public ?int $interventionSubtypeId;
    public ?int $interventionTypeId;
    public ?string $status;
    public ?string $description;
    public ?string $title;
    public ?int $materialId;
    public ?string $interventionDate;
    public ?string $comments;
    public ?string $solution;
    public ?string $materialName;
    public ?int $targetUserId;
    public ?string $targetUserName;
    public ?string $requesterUserName;

    public ?int $subtypeId;
    public ?string $subtypeName;

    public ?int $typeId;
    public ?string $typeName;

    /** @var HelperDto[] */
    public array $helpers = [];

    /** @var ServiceDto[] */
    public array $services = [];

    /** @var KeywordDto[] */
    public array $keywords = [];

    /** @var InterventionMessageDto[] */
    public array $messages = [];
}
