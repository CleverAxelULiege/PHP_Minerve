<?php

namespace App\Modules\Material;

use App\Database\Database;

class MaterialRepository
{
    public function __construct(private Database $database) {}

    public function getAll() {
        return $this->database->run("SELECT * FROM materials WHERE visible = true")->fetchAll();
    }
}
