<?php
namespace App\models;

class Currency extends BaseModel
{

    public function getAll() {
        return $this->executeQuery("SELECT * FROM currencies");
    }
}
