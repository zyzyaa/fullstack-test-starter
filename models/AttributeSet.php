<?php
namespace App\models;

class AttributeSet extends BaseModel
{

    public function getAll() {
        return $this->executeQuery("SELECT * FROM attribute_sets");
    }
}
