<?php
namespace App\models;

class Gallery extends BaseModel
{
    public function getAll() {
        return $this->executeQuery("SELECT * FROM product_gallery");
    }

    public function getByProductId($productId) {
        $query = "SELECT image_url FROM product_gallery WHERE product_id = ?";
        return $this->executePreparedQuery($query, [$productId], 's');
    }
}
