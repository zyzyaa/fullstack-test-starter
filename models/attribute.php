<?php
namespace App\models;

abstract class Attribute extends BaseModel
{
    abstract public function getByProductId($productId);
    
    public function getAllSets(): array {
        return $this->executeQuery("SELECT * FROM attribute_sets");
    }
    
    public function getAttributesBySet($set_id): array {
        $query = "SELECT * FROM attributes WHERE attribute_set_id = ?";
        return $this->executePreparedQuery($query, [$set_id], 'i');
    }
}
