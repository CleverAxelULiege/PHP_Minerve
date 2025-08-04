<?php

namespace App\Http\User;

use App\Database\Database;

class UserRepository
{
    public function __construct(private Database $database) {}


    public function getUDIStaff(){
        return $this->database->run("
            SELECT u.id AS user_id, CONCAT(u.lastname, ' ', u.firstname) AS user_name, u.ulg_id as user_ulg_id, surname AS user_surname
            FROM fapse_users AS u
            INNER JOIN departments_to_users AS utd ON utd.user_id = u.id
            WHERE utd.department_id = 2 AND u.visible = true;"
        )->fetchAll();
    }

    public function getAll() {
        return $this->database->run("SELECT * FROM fapse_users WHERE visible = true;")->fetchAll();
    }
}
