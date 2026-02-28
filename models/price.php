<?php
namespace App\models;

class Price extends BaseModel
{

    public function getAll() {
        return $this->executeQuery("SELECT * FROM prices");
    }

    public function getByProductId($productId) {
        $query = "
            SELECT p.amount, c.label, c.symbol
            FROM prices p
            JOIN currencies c ON p.currency_id = c.id
            WHERE p.product_id = ?
        ";
        return $this->executePreparedQuery($query, [$productId], 's');
    }

}
