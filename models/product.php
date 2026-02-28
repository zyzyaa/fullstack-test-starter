<?php
namespace App\models;

abstract class Product extends BaseModel
{

    abstract public function getAll();

    public function getByCategory($categoryName) {
        $query = "
            SELECT 
                p.id as product_id,
                p.name as product_name,
                p.description,
                p.in_stock,
                c.name as category_name,
                p.brand
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE c.name = ?
        ";

        $products = $this->executePreparedQuery($query, [$categoryName], 's');

        foreach ($products as &$p) {
            $p['prices'] = $this->getPrices($p['product_id']);
            $p['gallery'] = $this->getGallery($p['product_id']);
            $p['attributes'] = $this->getAttributes($p['product_id']);
        }

        return $products;
    }

    abstract protected function getPrices($product_id);
    abstract protected function getGallery($product_id);
    abstract protected function getAttributes($product_id);

    public function getProducts(): array {
        return $this->getAll();
    }
}
