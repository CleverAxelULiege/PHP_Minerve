<?php 

namespace App\Modules\Keyword;

use App\Database\Database;

class KeywordRepository {
    public function __construct(private Database $database)
    {
        
    }

    public function getAll(){
        return $this->database->run("SELECT id, name FROM keywords ORDER BY name")->fetchAll();
    }
}

?>