<?php

require(__DIR__ . "/Database.php");

$newDb = new Database("localhost", "new_udi6", "5432", "postgres", "admin");
$oldDb = new Database("localhost", "old_udi2", "5432", "postgres", "admin");

try{
    $newDb->beginTransaction();
    buildings($oldDb, $newDb);
    departments($oldDb, $newDb);
    users($oldDb, $newDb);
    connectUsersToDepartments($oldDb, $newDb);
    services($oldDb, $newDb);
    rooms($oldDb, $newDb);
    $newDb->commitTransaction();

} catch(Exception $e) {
    echo "err";
    $newDb->rollbackTransaction();
}

try{
    $newDb->beginTransaction();
    networks($oldDb, $newDb);
    $newDb->commitTransaction();
} catch(Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try{
    $newDb->beginTransaction();
    plugs($oldDb, $newDb);
    $newDb->commitTransaction();
} catch(Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try{
    $newDb->beginTransaction();
    distributors($oldDb, $newDb);
    $newDb->commitTransaction();
} catch(Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try{
    $newDb->beginTransaction();
    materials($oldDb, $newDb);
    $newDb->commitTransaction();
} catch(Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try{
    $newDb->beginTransaction();
    connectUsersToServices($oldDb, $newDb);
    $newDb->commitTransaction();
} catch(Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try{
    $newDb->beginTransaction();
    interventionTypes($oldDb, $newDb);
    interventionSubTypes($oldDb, $newDb);
    $newDb->commitTransaction();
} catch(Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    interventions($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    connectUsersHelpersToInterventions($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    connectUsersReadersToInterventions($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    connectUsersToMaterials($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    connectServiceToMaterials($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    keywords($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    connectKeywordsToIntervention($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    computers($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    softwares($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    connectSoftwaresToMaterial($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    agenda($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

try {
    $newDb->beginTransaction();
    connectMessagesToInterventions($oldDb, $newDb);
    $newDb->commitTransaction();
} catch (Exception $e) {
    var_dump($e->getMessage());
    $newDb->rollbackTransaction();
}

function connectMessagesToInterventions(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM msg");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_msg);
        $interventionId = cleanString($row->id_int);
        $author_user_id = cleanString($row->auteur_msg);
        $date = cleanString($row->date_msg);
        $time = cleanString($row->heure_msg);
        $createdAt = ($date && $time) ? "$date $time" : null;
        $isPublic = (int)cleanString($row->public_msg);
        $message = cleanString($row->message_msg);

        // Ensure both IDs exist
        $check = $new->run("SELECT 1 FROM interventions WHERE id = ?", [$interventionId])->fetch();
        $interventionExists = $check ? true : false;

        $check = $new->run("SELECT 1 FROM fapse_users WHERE id = ?", [$author_user_id])->fetch();
        $userExists = $check ? true : false;

        if(!$userExists) {
            $author_user_id = null;
        }


        $new->run(
            "INSERT INTO intervention_messages (
                intervention_id, author_user_id, message, is_public, created_at
            ) VALUES (?, ?, ?, ?, ?)",
            [$interventionId, $author_user_id, $message, $isPublic, $createdAt]
        );
    }

    $new->run("SELECT setval('intervention_messages_id_seq', (SELECT MAX(id) FROM intervention_messages))");
}


function agenda(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM agenda");

    while ($row = $stmtOld->fetchObject()) {
        $interventionDateUnix = cleanString($row->date);
        $interventionDate = $interventionDateUnix !== '' ? date('Y-m-d H:i:s', (int)$interventionDateUnix) : null;

        $interventionId = cleanString($row->interv);
        $requesterUserId = cleanString($row->personne);

        $assignedUserRaw = cleanString($row->udi);
        if ($assignedUserRaw === "u027235|u016842|u014144") {
            $assignedUserId = null;
        } else {
            $stmtUser = $new->run(
                "SELECT id FROM fapse_users WHERE ulg_id = ? ORDER BY id DESC LIMIT 1",
                [$assignedUserRaw]
            );
            $user = $stmtUser->fetchObject();
            $assignedUserId = $user ? $user->id : null;
        }

        $new->run(
            "INSERT INTO agenda (intervention_date, intervention_id, requester_user_id, assigned_user_id, comments)
             VALUES (?, ?, ?, ?, ?)",
            [$interventionDate, $interventionId, $requesterUserId, $assignedUserId, cleanString($row->commentaire)]
        );
    }
}


function connectSoftwaresToMaterial(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM lien_ordi_logi");

    while ($row = $stmtOld->fetchObject()) {
        $materialId = cleanString($row->id_ordi);
        $softwareId = cleanString($row->id_logi);
        $comments = cleanString($row->commentaire);
        $installationDate = cleanString($row->date_installation);

        $materialExists = $new->run("SELECT 1 FROM materials WHERE id = ?", [$materialId])->fetchColumn();
        $softwareExists = $new->run("SELECT 1 FROM softwares WHERE id = ?", [$softwareId])->fetchColumn();

        if ($materialExists && $softwareExists) {
            $new->run(
                "INSERT INTO materials_to_softwares (material_id, software_id, comments, installation_date)
                 VALUES (?, ?, ?, ?)",
                [$materialId, $softwareId, $comments, $installationDate ?: null]
            );
        } else {
            var_dump("doesnt exist");
        }
    }

    $new->run("SELECT setval('materials_to_softwares_id_seq', (SELECT MAX(id) FROM materials_to_softwares))");
}


function softwares(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM logiciel");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_logi);
        $name = cleanString($row->nom_logi);
        $type = cleanString($row->type_logi);
        $comments = cleanString($row->commentaire_logi);
        $visible = (int)cleanString($row->visible_logi);



        $new->run(
            "INSERT INTO softwares (id, name, type, comments, visible) VALUES (?, ?, ?, ?, ?)",
            [$id, $name, $type, $comments, $visible]
        );
    }

    $new->run("SELECT setval('softwares_id_seq', (SELECT MAX(id) FROM softwares))");
}


function computers(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM ordinateur");

    while ($row = $stmtOld->fetchObject()) {
        $materialId = cleanString($row->id_ordi);
        $operatingSystem = cleanString($row->os_ordi);
        $comments = cleanString($row->composant_ordi);
        $primaryWinsIp = cleanString($row->wins_pri_ordi);
        $secondaryWinsIp = cleanString($row->wins_sec_ordi);

        $stmt = $new->run("SELECT id FROM materials WHERE id = ?", [$materialId]);
        $materialExists = $stmt->fetchColumn();

        $materialIdToInsert = $materialExists !== false ? $materialId : null;

        $new->run(
            "INSERT INTO computers (material_id, operating_system, comments, primary_wins_ip, secondary_wins_ip)
             VALUES (?, ?, ?, ?, ?)",
            [$materialIdToInsert, $operatingSystem, $comments, $primaryWinsIp, $secondaryWinsIp]
        );
    }

    $new->run("SELECT setval('computers_id_seq', (SELECT MAX(id) FROM computers))");
}



function connectKeywordsToIntervention(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM lien_key_int");

    while ($row = $stmtOld->fetchObject()) {
        $interventionId = cleanString($row->id_int);
        $keywordId = cleanString($row->id_key);

        if ($interventionId === null || $keywordId === null) {
            continue;
        }

        $interventionExists = $new->run(
            "SELECT 1 FROM interventions WHERE id = ?",
            [$interventionId]
        )->fetchColumn();

        $keywordExists = $new->run(
            "SELECT 1 FROM keywords WHERE id = ?",
            [$keywordId]
        )->fetchColumn();

        if ($interventionExists && $keywordExists) {
            $new->run(
                "INSERT INTO interventions_to_keywords (intervention_id, keyword_id) VALUES (?, ?)",
                [$interventionId, $keywordId]
            );
        }
    }
}



function keywords(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM key");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_key);
        $name = cleanString($row->nom_key);

        if ($name === null) {
            continue;
        }

        $new->run(
            "INSERT INTO keywords (id, name) VALUES (?, ?)",
            [$id, $name]
        );
    }
}

function connectServiceToMaterials(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM lien_mat_serv");

    while ($row = $stmtOld->fetchObject()) {
        $serviceId = cleanString($row->id_serv);
        $materialId = cleanString($row->id_mat);

        if ($serviceId === null || $materialId === null) {
            continue;
        }

        $serviceExists = $new->run("SELECT 1 FROM services WHERE id = ?", [$serviceId])->fetchColumn();
        $materialExists = $new->run("SELECT 1 FROM materials WHERE id = ?", [$materialId])->fetchColumn();

        if (!$serviceExists || !$materialExists) {
            continue;
        }

        $new->run(
            "INSERT INTO materials_to_services (service_id, material_id)
             VALUES (?, ?)",
            [$serviceId, $materialId]
        );
    }
}


function connectUsersToMaterials(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM lien_mat_perso");

    while ($row = $stmtOld->fetchObject()) {
        $userId = cleanString($row->id_perso);
        $materialId = cleanString($row->id_mat);
        $isMainUser = (int)cleanString($row->principal);

        if ($userId === null || $materialId === null) {
            continue;
        }

        $userExists = $new->run("SELECT 1 FROM fapse_users WHERE id = ?", [$userId])->fetchColumn();
        $materialExists = $new->run("SELECT 1 FROM materials WHERE id = ?", [$materialId])->fetchColumn();

        if (!$userExists || !$materialExists) {
            continue;
        }

        $new->run(
            "INSERT INTO materials_to_users (user_id, material_id, is_main_user)
             VALUES (?, ?, ?)",
            [$userId, $materialId, $isMainUser]
        );
    }
}



function connectUsersReadersToInterventions(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM intervention");

    while ($row = $stmtOld->fetchObject()) {
        $readers = cleanString($row->lu_par);
        $interventionId = cleanString($row->id_int);

        if ($readers) {
            $arrayReaderUlgIds = explode("+", $readers);
            $arrayReaderUlgIds = array_map('trim', $arrayReaderUlgIds);
            $arrayReaderUlgIds = array_filter($arrayReaderUlgIds, fn($r) => $r !== "");

            foreach ($arrayReaderUlgIds as $ulgId) {
                $userId = $new->run(
                    "SELECT id FROM fapse_users WHERE ulg_id = ? ORDER BY id DESC LIMIT 1",
                    [$ulgId]
                )->fetchColumn();

                if ($userId !== false) {
                    $new->run(
                        "INSERT INTO readers_to_interventions (intervention_id, user_id)
                         VALUES (?, ?)",
                        [$interventionId, $userId]
                    );
                }
            }
        }
    }
}


function connectUsersHelpersToInterventions(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM intervention");

    while ($row = $stmtOld->fetchObject()) {
        $helpers = cleanString($row->intervenant);
        $interventionId = cleanString($row->id_int);

        if ($helpers) {
            $arrayHelpersId = explode("+", $helpers);
            $arrayHelpersId = array_map('trim', $arrayHelpersId);
            $arrayHelpersId = array_filter($arrayHelpersId, fn($h) => $h !== "");

            foreach ($arrayHelpersId as $userId) {
                $exists = $new->run(
                    "SELECT 1 FROM fapse_users WHERE id = ?",
                    [$userId]
                )->fetchColumn();

                if ($exists) {
                    $new->run(
                        "INSERT INTO helpers_to_interventions (intervention_id, user_id)
                         VALUES (?, ?)",
                        [$interventionId, $userId]
                    );
                }
            }
        }
    }
}


function interventions(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM intervention");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_int);
        $requestDate = trim(cleanString($row->date_demande) . " " . cleanString($row->heure_demande));
        $updatedAt = trim(cleanString($row->last_modif_d) . " " . cleanString($row->last_modif_h));
        $requestIp = cleanString($row->ip_demande);

        $ulgRequester = cleanString($row->no_mat_demande); // ulg_id
        $ulgTarget = cleanString($row->no_mat_int);
        $ulgLockedBy = cleanString($row->sc);

        $interventionSubtypeId = cleanString($row->no_req);
        $interventionTypeId = cleanString($row->no_treq);
        $status = cleanString($row->status_int);
        $description = cleanString($row->descr_int);
        $title = cleanString($row->bref_com);
        $materialId = cleanString($row->id_mat);
        $interventionDate = cleanString($row->date_int);

        $comments = cleanString($row->commentaire_int);
        $solution = cleanString($row->solution_int);

        $requestDate = $requestDate ? date('Y-m-d H:i:s', strtotime($requestDate)) : null;
        $updatedAt = $updatedAt ? date('Y-m-d H:i:s', strtotime($updatedAt)) : null;
        $interventionDate = $interventionDate ? date('Y-m-d', strtotime($interventionDate)) : null;



        $requesterUserId = $ulgRequester
            ? $new->run("SELECT id FROM fapse_users WHERE ulg_id = ? ORDER BY id DESC LIMIT 1", [$ulgRequester])->fetchColumn()
            : null;

        $interventionTargetUserId = $ulgTarget
            ? $new->run("SELECT id FROM fapse_users WHERE ulg_id = ? ORDER BY id DESC LIMIT 1", [$ulgTarget])->fetchColumn()
            : null;

        $lockedByUserId = $ulgLockedBy
            ? $new->run("SELECT id FROM fapse_users WHERE ulg_id = ? ORDER BY id DESC LIMIT 1", [$ulgLockedBy])->fetchColumn()
            : null;

        $interventionSubtypeId = $new->run("SELECT id FROM intervention_subtypes WHERE id = ?", [$interventionSubtypeId])->fetchColumn() ?: null;
        $interventionTypeId = $new->run("SELECT id FROM intervention_types WHERE id = ?", [$interventionTypeId])->fetchColumn() ?: null;
        $materialId = $new->run("SELECT id FROM materials WHERE id = ?", [$materialId])->fetchColumn() ?: null;

        if ($requesterUserId === false) {
            $requesterUserId = null;
        }

        if ($interventionTargetUserId === false) {
            $interventionTargetUserId = null;
        }

        if ($lockedByUserId === false) {
            $lockedByUserId = null;
        }

        $new->run(
            "INSERT INTO interventions (
                id, request_date, updated_at, request_ip,
                requester_user_id, intervention_target_user_id, locked_by_user_id,
                intervention_subtype_id,
                status, description, title, material_id, intervention_date,
                comments, solution, intervention_type_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [
                $id,
                $requestDate,
                $updatedAt,
                $requestIp,
                $requesterUserId,
                $interventionTargetUserId,
                $lockedByUserId,
                $interventionSubtypeId,
                $status,
                $description,
                $title,
                $materialId,
                $interventionDate,
                $comments,
                $solution,
                $interventionTypeId
            ]
        );
    }

    $new->run("SELECT setval('interventions_id_seq', (SELECT MAX(id) FROM interventions))");
}


function interventionTypes(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM type_req");
    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_treq);
        $name = cleanString($row->nom_treq);

        if ($name === null)
            continue;

        $new->run(
            "INSERT INTO intervention_types (id, name) VALUES (?, ?)",
            [$id, $name]
        );
    }

    $new->run("SELECT setval('intervention_types_id_seq', (SELECT MAX(id) FROM intervention_types))");
}

function interventionSubTypes(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM req");
    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_req);
        $name = cleanString($row->nom_req);
        $genericSolution = cleanString($row->solution_req);
        $interventionTypeId = cleanString($row->id_treq);
        $visible = (int)cleanString($row->visible_req);

        if ($name === null)
            continue;

        // Verify that the related intervention type exists
        // $typeExists = $new->run("SELECT 1 FROM intervention_types WHERE id = ?", [$interventionTypeId])->fetchColumn();
        // if (!$typeExists) {
        //     continue;
        // }

        $new->run(
            "INSERT INTO intervention_subtypes (id, intervention_type_id, name, generic_solution, visible)
             VALUES (?, ?, ?, ?, ?)",
            [$id, $interventionTypeId, $name, $genericSolution, $visible]
        );
    }

    $new->run("SELECT setval('intervention_subtypes_id_seq', (SELECT MAX(id) FROM intervention_subtypes))");
}


function connectUsersToServices(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM lien_perso_serv");

    while ($row = $stmtOld->fetchObject()) {
        $userId = cleanString($row->id_perso);
        $serviceId = cleanString($row->id_serv);

        $userExists = $new->run("SELECT 1 FROM fapse_users WHERE id = ?", [$userId])->fetchColumn();
        $serviceExists = $new->run("SELECT 1 FROM services WHERE id = ?", [$serviceId])->fetchColumn();

        if ($userExists && $serviceExists) {
            $new->run(
                "INSERT INTO services_to_users (user_id, service_id) VALUES (?, ?)",
                [$userId, $serviceId]
            );
        }
    }
}



function materials(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM materiel");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_mat);
        $ulgMark = cleanString($row->ulgmark_mat);
        $brand = cleanString($row->marque_mat);
        $model = cleanString($row->modele_mat);
        $comments = cleanString($row->commentaire_mat);
        $serialNumber = cleanString($row->sn_mat);
        $distributorSerialNumber = cleanString($row->sn_mat_fourn);
        $domain = cleanString($row->domaine_mat);
        $identificationCode = cleanString($row->identification_mat);
        $identificationNumber = cleanString($row->no_mat);
        $wifiMacAddress = cleanString($row->zone_appeltalk_mat);
        $dockMacAddress = cleanString($row->nom_appeltalk_mat);
        $ethMacAddress = cleanString($row->mac_mat);
        $deployementDate = cleanString($row->date_service_mat);
        $price = cleanString($row->pa_mat);
        $purchaseOrder = cleanString($row->no_com_mat);
        $roomId = cleanString($row->nom_loc_mat);
        $isMobile = (int)cleanString($row->mobile_mat);
        $plugId = cleanString($row->id_prise);
        $distributorId = cleanString($row->id_distri);
        $visible = (int)cleanString($row->visible_mat);
        $type = cleanString($row->type);
        $externNetIdentityId = cleanString($row->id_netidentity);

        $plugId = $new->run("SELECT id FROM plugs WHERE id = ?", [$plugId])->fetchColumn() ?: null;

        $roomId = $new->run("SELECT id FROM rooms WHERE id = ?", [$roomId])->fetchColumn() ?: null;

        $distributorId = $new->run("SELECT id FROM distributors WHERE id = ?", [$distributorId])->fetchColumn() ?: null;

        // Insert into materials
        $materialId = $new->run(
            "INSERT INTO materials (
                id, ulg_mark, brand, model, type, identification_code, identification_number,
                serial_number, distributor_serial_number, domain, plug_id, room_id, price,
                purchase_order, deployment_date, extern_netidentity_id, is_mobile, comments, visible
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            RETURNING id",
            [
                $id,
                $ulgMark,
                $brand,
                $model,
                $type,
                $identificationCode,
                $identificationNumber,
                $serialNumber,
                $distributorSerialNumber,
                $domain,
                $plugId,
                $roomId,
                $price,
                $purchaseOrder,
                $deployementDate ?: null,
                $externNetIdentityId,
                $isMobile,
                $comments,
                $visible
            ]
        )->fetchColumn();

        if ($ethMacAddress) {
            $new->run(
                "INSERT INTO material_mac_addresses (material_id, mac_type, mac_address)
                 VALUES (?, 'eth', ?)",
                [$materialId, $ethMacAddress]
            );
        }

        if ($dockMacAddress) {
            $new->run(
                "INSERT INTO material_mac_addresses (material_id, mac_type, mac_address)
                 VALUES (?, 'dock', ?)",
                [$materialId, $dockMacAddress]
            );
        }

        if ($wifiMacAddress) {
            $new->run(
                "INSERT INTO material_mac_addresses (material_id, mac_type, mac_address)
                 VALUES (?, 'wifi', ?)",
                [$materialId, $wifiMacAddress]
            );
        }
    }

    $new->run("SELECT setval('materials_id_seq', (SELECT MAX(id) FROM materials))");
    $new->run("SELECT setval('material_mac_addresses_id_seq', (SELECT MAX(id) FROM material_mac_addresses))");
}



function distributors(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM distributeur");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_distri);
        $name = cleanString($row->nom_distri);
        $street = cleanString($row->rue_distri);
        $zip = cleanString($row->cp_distri);
        $city = cleanString($row->loc_distri);
        $country = cleanString($row->pays_ditri);
        $fax = cleanString($row->fax_distri);
        $comments = cleanString($row->commentaire_distri);
        $visible = (int)cleanString($row->visible_distri);

        $salesPhoneNumber = cleanString($row->tel_com_distri);
        $salesContactName = cleanString($row->contact_com_distri);
        $salesEmail = cleanString($row->email_com_distri);

        $techPhoneNumber = cleanString($row->tel_tech_distri);
        $techContactName = cleanString($row->contact_tech_distri);
        $techEmail = cleanString($row->email_tech_distri);

        $distributorId = $new->run(
            "INSERT INTO distributors (
                id, name, street, zip, city, country, fax, comments, visible
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            RETURNING id",
            [$id, $name, $street, $zip, $city, $country, $fax, $comments, $visible]
        )->fetchColumn();

        if ($salesPhoneNumber || $salesContactName || $salesEmail) {
            $new->run(
                "INSERT INTO distributor_contacts (
                    distributor_id, contact_type, contact_name, phone_number, email
                ) VALUES (?, 'sales', ?, ?, ?)",
                [$distributorId, $salesContactName, $salesPhoneNumber, $salesEmail]
            );
        }

        if ($techPhoneNumber || $techContactName || $techEmail) {
            $new->run(
                "INSERT INTO distributor_contacts (
                    distributor_id, contact_type, contact_name, phone_number, email
                ) VALUES (?, 'tech', ?, ?, ?)",
                [$distributorId, $techContactName, $techPhoneNumber, $techEmail]
            );
        }
    }

    $new->run("SELECT setval('distributors_id_seq', (SELECT MAX(id) FROM distributors))");
    $new->run("SELECT setval('distributor_contacts_id_seq', (SELECT MAX(id) FROM distributor_contacts))");
}



function plugs(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM prise");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_prise);
        $code = cleanString($row->no_prise);

        $dnsName = cleanString($row->nom_dns_prise);
        $ipAddress = cleanString($row->ip_prise);
        $externPlugId = cleanString($row->alias2_prise);
        $alias = cleanString($row->alias1_prise);
        $networkBranch = cleanString($row->branche_reseau_prise);
        $paid = (int)cleanString($row->payee_prise);
        $actionDate = cleanString($row->date_acti_prise);
        $comments = cleanString($row->commentaire_prise);
        $history = cleanString($row->histo_pri);
        $active = (int)cleanString($row->active_prise);
        $visible = (int)cleanString($row->visible_prise);
        $subnetMask = cleanString($row->masque_prise);
        $gatewayIp = cleanString($row->passerelle_prise);
        $firstDns = cleanString($row->dns1_prise);
        $secondDns = cleanString($row->dns2_prise);

        $networkId = cleanString($row->id_reseau);
        $networkId = $new->run("SELECT id FROM networks WHERE id = ?", [$networkId])->fetchColumn() ?: null;

        $roomId = cleanString($row->id_loc);
        $roomId = $new->run("SELECT id FROM rooms WHERE id = ?", [$roomId])->fetchColumn() ?: null;

        $serviceId = cleanString($row->service_prise);
        $serviceId = $new->run("SELECT id FROM services WHERE id = ?", [$serviceId])->fetchColumn() ?: null;

        $new->run(
            "INSERT INTO plugs (
                id, code, network_id, room_id, service_id,
                dns_name, ip_address, extern_plug_id, alias, network_branch,
                paid, activation_date, comments, history, active,
                visible, first_dns, second_dns, subnet_mask, gateway_ip
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?
            )",
            [
                $id,
                $code,
                $networkId,
                $roomId,
                $serviceId,
                $dnsName,
                $ipAddress,
                $externPlugId,
                $alias,
                $networkBranch,
                $paid,
                $actionDate ?: null,
                $comments,
                $history,
                $active,
                $visible,
                $firstDns,
                $secondDns,
                $subnetMask,
                $gatewayIp
            ]
        );
    }

    $new->run("SELECT setval('plugs_id_seq', (SELECT MAX(id) FROM plugs))");
}



function networks(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM reseaux");

    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_reseau);
        $name = cleanString($row->nom_reseau);
        $plugCode = cleanString($row->id_reseau_plugs);
        $firewallZone = cleanString($row->zone_firewall);
        $gatewayIp = cleanString($row->passerelle);
        $subnetMask = cleanString($row->masque);
        $comments = cleanString($row->commentaire);
        $visible = (int)cleanString($row->visible);

        $buildingId = null;
        $buildingName = cleanString($row->batiments);

        if ($buildingName) {
            $buildingId = $new->run(
                "SELECT id FROM buildings WHERE location_code ILIKE ?",
                [$buildingName]
            )->fetchColumn();

            if ($buildingId === false) {
                $buildingId = null;
                $buildingId = $new->run(
                    "INSERT INTO buildings (location_code) VALUES (?) RETURNING id",
                    [$buildingName]
                )->fetchColumn();
                // $incrementalId++;
            }
        }

        $new->run(
            "INSERT INTO networks (id, name, plug_code, firewall_zone, gateway_ip, subnet_mask, comments, visible, building_id)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$id, $name, $plugCode, $firewallZone, $gatewayIp, $subnetMask, $comments, $visible, $buildingId]
        );
    }

    $new->run("SELECT setval('networks_id_seq', (SELECT MAX(id) FROM networks))");
}


function rooms(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM local");
    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_loc);
        $name = cleanString($row->nom_loc);
        $hasAlarm = (int)cleanString($row->alarme_loc);
        $comments = cleanString($row->commentaire_loc);
        $buildingId = cleanString($row->id_bat);
        $visible = cleanString($row->visible_loc);

        $new->run(
            "INSERT INTO rooms (id, name, building_id, has_alarm, comments, visible)
            VALUES (?, ?, ?, ?, ?, ?)",
            [$id, $name, $buildingId, $hasAlarm, $comments, $visible]
        );
    }

    $new->run("SELECT setval('rooms_id_seq', (SELECT MAX(id) FROM rooms))");
}

function services(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM service");
    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_serv);
        $userManagerId = cleanString($row->id_responsable_serv);
        $name = cleanString($row->nom_serv);
        $fullName = cleanString($row->nom_complet_serv);

        $oldDepartmentId = cleanString($row->id_dep);
        $newDepartmentId = $oldDepartmentId;


        $websiteUrl = cleanString($row->site_serv);
        $websiteIpAddress = cleanString($row->ip_site_serv);
        $comments = cleanString($row->commentaire_serv);
        $visible = (int)cleanString($row->visible_serv);
        $registrationCode = (int)cleanString($row->matricule);

        $new->run(
            "INSERT INTO services (id, name, fullname, manager_user_id, department_id, website_url, website_ip_address, registration_code, comments, visible)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$id, $name, $fullName, $userManagerId, $newDepartmentId, $websiteUrl, $websiteIpAddress, $registrationCode, $comments, $visible]
        );
    }

    $new->run("SELECT setval('services_id_seq', (SELECT MAX(id) FROM services))");
}

function connectUsersToDepartments(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT 
            d.id_dep, 
            p.*
        FROM 
            perso_fapse p
        JOIN 
            departement d ON CAST(p.departement AS INTEGER) = d.id_dep");

    while ($row = $stmtOld->fetchObject()) {
        $oldUser = mapToOldUser($row);

        $newUserId = $oldUser->id;
        $newDepartmentId = $row->id_dep;
        if ($newUserId && $newDepartmentId) {
            $new->run("INSERT INTO departments_to_users (user_id, department_id)
                VALUES (?, ?)", [$newUserId, $newDepartmentId]);
        }
    }
}

function users(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM perso_fapse ORDER BY id_perso");
    while ($row = $stmtOld->fetchObject()) {
        $oldUser = mapToOldUser($row);

        $new->run("INSERT INTO fapse_users (
        id, ulg_id, lastname, firstname, surname, email, phone_number,
        personal_directory, comments, visible, reachable
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?,
        ?, ?, ?, ?
    )", [
            $oldUser->id,
            $oldUser->ulgId,
            $oldUser->lastname,
            $oldUser->firstname,
            $oldUser->surname,
            $oldUser->email,
            $oldUser->phone,
            $oldUser->persDirectory,
            $oldUser->comments,
            $oldUser->visible,
            $oldUser->reachable,
        ]);
    }

    $new->run("SELECT setval('fapse_users_id_seq', (SELECT MAX(id) FROM fapse_users))");
}

function buildings(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM batiment ORDER BY id_bat");
    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_bat);
        $name = cleanString($row->nom_bat);
        $locationCode = cleanString($row->no_bat);
        $parkingCode = cleanString($row->park_bat);
        $comments = cleanString($row->commentaire_bat);
        $visible = (bool)cleanString($row->visible_bat);

        $new->run(
            "INSERT INTO buildings (id, name, location_code, parking_code, comments, visible)
            VALUES (?, ?, ?, ?, ?, ?)",
            [$id, $name, $locationCode, $parkingCode, $comments, (int)$visible]
        );
    }

    $new->run("SELECT setval('buildings_id_seq', (SELECT MAX(id) FROM buildings))");
}

function departments(Database $old, Database $new)
{
    $stmtOld = $old->run("SELECT * FROM departement ORDER BY id_dep");
    while ($row = $stmtOld->fetchObject()) {
        $id = cleanString($row->id_dep);
        $name = cleanString($row->nom_dep);
        $fullname = cleanString($row->nom_dep);
        $president = cleanString($row->nom_dep);
        $comments = cleanString($row->nom_dep);
        $visible = (int)cleanString($row->nom_dep);

        $new->run("INSERT INTO departments (id, name, fullname, president, comments, visible)
                VALUES (?, ?, ?, ?, ?, ?)", [$id, $name, $fullname, $president, $comments, $visible]);
    }

    $new->run("SELECT setval('departments_id_seq', (SELECT MAX(id) FROM departments))");
}

function cleanString($value)
{
    if (is_null($value))
        return null;

    $value = (string)$value;
    $value = trim(html_entity_decode($value));
    if ($value == "") {
        return null;
    }

    return $value;
}

function findNewUserId(Database $new, OldUser $oldUser): ?int
{
    $sql = "
        SELECT id 
        FROM fapse_users 
        WHERE 
            ulg_id IS NOT DISTINCT FROM ? AND
            lastname IS NOT DISTINCT FROM ? AND
            firstname IS NOT DISTINCT FROM ? AND
            surname IS NOT DISTINCT FROM ? AND
            email IS NOT DISTINCT FROM ? AND
            phone_number IS NOT DISTINCT FROM ? AND
            personal_directory IS NOT DISTINCT FROM ? AND
            comments IS NOT DISTINCT FROM ?
    ";

    $params = [
        $oldUser->ulgId,
        $oldUser->lastname,
        $oldUser->firstname,
        $oldUser->surname,
        $oldUser->email,
        $oldUser->phone,
        $oldUser->persDirectory,
        $oldUser->comments,
    ];

    return $new->run($sql, $params)->fetchColumn() ?: null;
}




function mapToOldUser(object $row): OldUser
{
    return new OldUser(
        cleanString($row->id_perso),
        cleanString($row->no_matricule_perso),
        cleanString($row->nom_complet_perso),
        cleanString($row->prenom_perso),
        cleanString($row->surnom_perso),
        cleanString($row->no_tel_perso),
        cleanString($row->email_perso),
        cleanString($row->rep_perso),
        cleanString($row->commentaire_perso),
        (int) cleanString($row->visible_perso),
        (int) cleanString($row->joignable),
    );
}


class OldUser
{
    public function __construct(
        public ?string $id,
        public ?string $ulgId,
        public ?string $lastname,
        public ?string $firstname,
        public ?string $surname,
        public ?string $phone,
        public ?string $email,
        public ?string $persDirectory,
        public ?string $comments,
        public int $visible,
        public int $reachable,
    ) {}
}
