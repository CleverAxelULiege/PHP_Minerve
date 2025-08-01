<?php

namespace App\Intervention  ;

use App\Intervention\DTOs\InterventionDto;
use App\Intervention\DTOs\HelperDto;
use App\Intervention\DTOs\ServiceDto;
use App\Intervention\DTOs\KeywordDto;

class InterventionMapper
{
    public static function mapToDto(object $data): InterventionDto
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
            if($data->$source === null){
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
}
