<?php

namespace App\Http\Intervention;

use App\Database\Database;

class InterventionRepository
{
    private Database $db;

    public function __construct(Database $database)
    {
        $this->db = $database;
    }

    public function getTotalInterventionsCount(): int
    {
        $sql = "SELECT COUNT(*) as total FROM interventions";
        return (int) $this->db->run($sql)->fetchObject()->total;
    }

    public function getPaginatedInterventions(int $page, int $resultsPerPage)
    {
        $offset = ($page - 1) * $resultsPerPage;
        $sql = "
            SELECT
                i.*,
                m.id as material_id,
                CONCAT(m.identification_number, '-', m.identification_code) AS material_name,
                target_user.id AS target_user_id,
                requester_user.id AS requester_user_id,
                CONCAT(target_user.firstname, ' ', target_user.surname) AS target_user_name,
                CONCAT(requester_user.firstname, ' ', requester_user.surname) AS requester_user_name,
                ist.name AS subtype_name,
                ist.id AS subtype_id,
                COALESCE(it_via_subtype.name, it_direct.name) as type_name,
                COALESCE(it_via_subtype.id, it_direct.id) as type_id
            FROM interventions i
            LEFT JOIN fapse_users target_user ON target_user.id = i.intervention_target_user_id
            LEFT JOIN fapse_users requester_user ON requester_user.id = i.requester_user_id
            LEFT JOIN intervention_subtypes ist ON ist.id = i.intervention_subtype_id
            LEFT JOIN intervention_types it_via_subtype ON it_via_subtype.id = ist.intervention_type_id
            LEFT JOIN intervention_types it_direct ON it_direct.id = i.intervention_type_id
            LEFT JOIN materials m ON m.id = i.material_id
            ORDER BY i.id DESC
            LIMIT ? OFFSET ?
            ";
        $interventions = $this->db->run($sql, [$resultsPerPage, $offset])->fetchAll();
        $interventionIds = array_column($interventions, 'id');
        $userIds = array_column($interventions, 'intervention_target_user_id');
        $allHelpers = $this->getBatchHelpers($interventionIds);
        $allServices = $this->getBatchServices($userIds);
        $allKeywords = $this->getBatchKeywords($interventionIds);
        foreach ($interventions as &$intervention) {
            $intervention->helpers = $allHelpers[$intervention->id] ?? [];
            $intervention->services = $allServices[$intervention->intervention_target_user_id] ?? [];
            $intervention->keywords = $allKeywords[$intervention->id] ?? [];
        }
        return $interventions;
    }


    public function getInterventionWithDetails($interventionId)
    {
        $intervention = $this->getBaseIntervention($interventionId);

        if (!$intervention) {
            return null;
        }

        $intervention->helpers = $this->getInterventionHelpers($interventionId);
        $intervention->services = $this->getUserServices($intervention->intervention_target_user_id);
        $intervention->keywords = $this->getInterventionKeywords($interventionId);

        return $intervention;
    }

    public function getInterventionsWithDetails(array $interventionIds)
    {
        $interventions = $this->getBaseInterventions($interventionIds);

        $allHelpers = $this->getBatchHelpers($interventionIds);
        $userIds = array_column($interventions, 'intervention_target_user_id');
        $allServices = $this->getBatchServices($userIds);
        $allKeyWords = $this->getBatchKeywords($interventionIds);

        foreach ($interventions as &$intervention) {
            $intervention->helpers = $allHelpers[$intervention->id] ?? [];
            $intervention->services = $allServices[$intervention->intervention_target_user_id] ?? [];
            $intervention->keywords = $allKeyWords[$intervention->id] ?? "";
        }

        return $interventions;
    }

    private function getBaseIntervention($interventionId)
    {
        $sql = "
        SELECT
            i.*,
            m.id as material_id,
            CONCAT(m.identification_number, '-', m.identification_code) AS material_name,
            target_user.id AS target_user_id,
            requester_user.id AS requester_user_id,
            CONCAT(target_user.firstname, ' ', target_user.surname) AS target_user_name,
            CONCAT(requester_user.firstname, ' ', requester_user.surname) AS requester_user_name,
            ist.name AS subtype_name,
            ist.id AS subtype_id,
            COALESCE(it_via_subtype.name, it_direct.name) as type_name,
            COALESCE(it_via_subtype.id, it_direct.id) as type_id
        FROM interventions i
        LEFT JOIN fapse_users target_user ON target_user.id = i.intervention_target_user_id
        LEFT JOIN fapse_users requester_user ON requester_user.id = i.requester_user_id
        LEFT JOIN intervention_subtypes ist ON ist.id = i.intervention_subtype_id
        LEFT JOIN intervention_types it_via_subtype ON it_via_subtype.id = ist.intervention_type_id
        LEFT JOIN intervention_types it_direct ON it_direct.id = i.intervention_type_id
        LEFT JOIN materials m ON m.id = i.material_id
        WHERE i.id = ?
    ";
        return $this->db->run($sql, [$interventionId])->fetchObject();
    }

    private function getInterventionHelpers($interventionId)
    {
        $sql = "
            SELECT DISTINCT h.id, h.surname
            FROM helpers_to_interventions ith
            JOIN fapse_users h ON h.id = ith.user_id
            WHERE ith.intervention_id = ?
        ";

        return $this->db->run($sql, [$interventionId])->fetchAll();
    }

    private function getUserServices($userId)
    {
        $sql = "
            SELECT DISTINCT s.id, s.name
            FROM services_to_users uts
            JOIN services s ON s.id = uts.service_id
            WHERE uts.user_id = ?
        ";

        return $this->db->run($sql, [$userId])->fetchAll();
    }



    private function getBaseInterventions(array $interventionIds)
    {
        $placeholders = str_repeat('?,', count($interventionIds) - 1) . '?';
        $sql = "
        SELECT
            i.*,
            m.id as material_id,
            CONCAT(m.identification_number, '-', m.identification_code) AS material_name,
            target_user.id AS target_user_id,
            requester_user.id AS requester_user_id,
            CONCAT(target_user.firstname, ' ', target_user.surname) AS target_user_name,
            CONCAT(requester_user.firstname, ' ', requester_user.surname) AS requester_user_name,
            ist.name AS subtype_name,
            ist.id AS subtype_id,
            COALESCE(it_via_subtype.name, it_direct.name) as type_name,
            COALESCE(it_via_subtype.id, it_direct.id) as type_id
        FROM interventions i
        LEFT JOIN fapse_users target_user ON target_user.id = i.intervention_target_user_id
        LEFT JOIN fapse_users requester_user ON requester_user.id = i.requester_user_id
        LEFT JOIN intervention_subtypes ist ON ist.id = i.intervention_subtype_id
        LEFT JOIN intervention_types it_via_subtype ON it_via_subtype.id = ist.intervention_type_id
        LEFT JOIN intervention_types it_direct ON it_direct.id = i.intervention_type_id
        LEFT JOIN materials m ON m.id = i.material_id
        WHERE i.id IN ($placeholders)
        ORDER BY i.id
    ";
        return $this->db->run($sql, $interventionIds)->fetchAll();
    }

    private function getBatchHelpers(array $interventionIds)
    {
        $placeholders = str_repeat('?,', count($interventionIds) - 1) . '?';
        $sql = "
            SELECT 
                ith.intervention_id,
                h.id, 
                h.surname
            FROM helpers_to_interventions ith
            JOIN fapse_users h ON h.id = ith.user_id
            WHERE ith.intervention_id IN ($placeholders)
        ";

        $results = $this->db->run($sql, $interventionIds)->fetchAll();

        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row->intervention_id][] = $row;
        }

        return $grouped;
    }

    private function getBatchServices(array $userIds)
    {
        $placeholders = str_repeat('?,', count($userIds) - 1) . '?';
        $sql = "
            SELECT 
                uts.user_id,
                s.id, 
                s.name
            FROM services_to_users uts
            JOIN services s ON s.id = uts.service_id
            WHERE uts.user_id IN ($placeholders)
        ";

        $results = $this->db->run($sql, $userIds)->fetchAll();

        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row->user_id][] = $row;
        }

        return $grouped;
    }

    private function getInterventionKeywords($interventionId)
    {
        $sql = "
            SELECT DISTINCT k.id, k.name
            FROM interventions_to_keywords itk
            JOIN keywords k ON k.id = itk.keyword_id
            WHERE itk.intervention_id = ?
        ";

        return $this->db->run($sql, [$interventionId])->fetchAll();
    }

    private function getBatchKeywords(array $interventionIds)
    {
        $placeholders = str_repeat('?,', count($interventionIds) - 1) . '?';
        $sql = "
            SELECT 
                itk.intervention_id,
                k.id, 
                k.name
            FROM interventions_to_keywords itk
            JOIN keywords k ON k.id = itk.keyword_id
            WHERE itk.intervention_id IN ($placeholders)
        ";

        $results = $this->db->run($sql, $interventionIds)->fetchAll();

        $grouped = [];
        foreach ($results as $row) {
            $grouped[$row->intervention_id][] = $row;
        }

        return $grouped;
    }

    public function getInterventionTypes()
    {
        return $this->db->run("
        SELECT 
        it.id AS intervention_id,
        it.name AS intervention_name,
        JSON_AGG(
            JSON_BUILD_OBJECT(
                'id', ist.id,
                'name', ist.name,
                'generic_solution', ist.generic_solution
            )
        ) AS intervention_subtypes
        FROM intervention_types AS it
        INNER JOIN intervention_subtypes AS ist ON ist.intervention_type_id = it.id
        WHERE ist.visible = true
        GROUP BY it.id
        ORDER BY it.name")
            ->fetchAll();
    }

    public function getInterventionMessages($interventionId)
    {
        return $this->db->run("
        SELECT 
        im.id as message_id,
        im.message as message,
        im.is_public as message_public,
        im.created_at as message_created_at,
        im.updated_at as message_updated_at,
        u.id AS user_id,
        u.firstname as user_firstname,
        u.ulg_id as user_ulg_id,
        u.lastname as user_lastname
        FROM intervention_messages AS im
        LEFT JOIN fapse_users AS u ON im.author_user_id = u.id WHERE im.intervention_id = ?", [$interventionId])->fetchAll();
    }
}
