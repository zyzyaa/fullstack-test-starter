<?php
namespace App\models;

class ProductAttributeSet extends BaseModel
{

    public function getAll() {
        return $this->executeQuery("SELECT * FROM product_attribute_sets");
    }
}
