<?php
namespace App\models;

class TextAttribute extends Attribute
{
    public function getByProductId($productId): array {
        $query = "
            SELECT DISTINCT a.display_value, a.value, s.name AS set_name, s.type AS set_type
            FROM attributes a
            JOIN product_attributes pa ON pa.attribute_id = a.id
            JOIN attribute_sets s ON a.attribute_set_id = s.id
            WHERE pa.product_id = ? AND s.type = 'text'
        ";
        return $this->executePreparedQuery($query, [$productId], 's');
    }
}