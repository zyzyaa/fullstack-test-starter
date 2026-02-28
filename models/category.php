<?php
namespace App\models;

class Category extends BaseModel
{
    public function getAll() {
        return $this->executeQuery("SELECT * FROM categories");
    }
}
