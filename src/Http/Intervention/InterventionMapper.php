<?php

namespace App\Http\Intervention;

use App\Http\Intervention\DTOs\AuthorMessageDto;
use App\Http\Intervention\DTOs\HelperDto;
use App\Http\Intervention\DTOs\KeywordDto;
use App\Http\Intervention\DTOs\ServiceDto;
use App\Http\Intervention\DTOs\InterventionDto;
use App\Http\Intervention\DTOs\InterventionMessageDto;
use App\Http\Intervention\DTOs\InterventionTypeDto;
use App\Http\Intervention\DTOs\InterventionSubtypeDto;

class InterventionMapper
{
    public static function mapToInterventionDto(object $data): InterventionDto
    {
        $dto = new InterventionDto();

        $map = [
            "id" => "id",
            "request_date" => "requestDate",
            "updated_at" => "updatedAt",
            "request_ip" => "requestIp",
            "requester_user_id" => "requesterUserId",
            "intervention_target_user_id" => "interventionTargetUserId",
            "locked_by_user_id" => "lockedByUserId",
            "intervention_subtype_id" => "interventionSubtypeId",
            "intervention_type_id" => "interventionTypeId",
            "status" => "status",
            "description" => "description",
            "title" => "title",
            "material_id" => "materialId",
            "intervention_date" => "interventionDate",
            "comments" => "comments",
            "solution" => "solution",
            "material_name" => "materialName",
            "target_user_id" => "targetUserId",
            "target_user_name" => "targetUserName",
            "requester_user_name" => "requesterUserName",
            "subtype_name" => "subtypeName",
            "subtype_id" => "subtypeId",
            "type_name" => "typeName",
            "type_id" => "typeId",
        ];

        foreach ($map as $source => $target) {
            if ($data->$source === null) {
                $dto->$target = null;
            } else {
                $dto->$target = $data->$source;
            }
        }

        $dto->helpers = array_map(function ($h) {
            $helper = new HelperDto();
            $helper->id = $h->id;
            $helper->surname = $h->surname;
            return $helper;
        }, $data->helpers ?? []);

        $dto->services = array_map(function ($s) {
            $service = new ServiceDto();
            $service->id = $s->id;
            $service->name = $s->name;
            return $service;
        }, $data->services ?? []);

        $dto->keywords = array_map(function ($k) {
            $keyword = new KeywordDto();
            $keyword->id = $k->id;
            $keyword->name = $k->name;
            return $keyword;
        }, $data->keywords ?? []);

        return $dto;
    }

    public static function fromRawToType(object $rawData): InterventionTypeDto
    {
        $dto = new InterventionTypeDto();
        $dto->id = $rawData->intervention_id;
        $dto->name = $rawData->intervention_name;

        $subtypes = json_decode($rawData->intervention_subtypes) ?? [];

        usort($subtypes, fn($a, $b) => $a->name <=> $b->name);

        $dto->subTypes = array_map(function ($subtype) {
            $subDto = new InterventionSubtypeDto();
            $subDto->id = $subtype->id;
            $subDto->name = $subtype->name;
            $subDto->genericSolution = $subtype->generic_solution;
            return $subDto;
        }, $subtypes);

        return $dto;
    }

    public static function mapToMessageDto(object $row): InterventionMessageDto
    {
        return new InterventionMessageDto(
            id: (int) $row->message_id,
            message: $row->message ?? null,
            isPublic: (bool) $row->message_public,
            createdAt: $row->message_created_at,
            updatedAt: $row->message_updated_at,
            author: new AuthorMessageDto(
                id: $row->user_id,
                firstName: $row->user_firstname ?? null,
                lastName: $row->user_lastname ?? null,
                ulgId: $row->user_ulg_id ?? null
            )
        );
    }
}
