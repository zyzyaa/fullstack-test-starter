<?php
namespace App\models;

class ClothesCategory extends BaseModel
{
    public function getAll(): array {
        return $this->loadProductsByCategory('clothes');
    }

    private function loadProductsByCategory(string $categoryName): array {
        $q = "
            SELECT p.id AS product_id, p.name AS product_name, p.description, p.in_stock,
                   c.name AS category_name, p.brand
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            WHERE c.name = ?
        ";
        $products = $this->executePreparedQuery($q, [$categoryName], 's');
        foreach ($products as &$p) {
            $p['prices']     = $this->getPrices($p['product_id']);
            $p['gallery']    = $this->getGallery($p['product_id']);
            $p['attributes'] = $this->getAttributes($p['product_id']);
        }
        return $products;
    }

    public function getPrices($product_id) {
        $q = "SELECT pr.amount, cur.label, cur.symbol
              FROM prices pr LEFT JOIN currencies cur ON pr.currency_id = cur.id
              WHERE pr.product_id = ?";
        return $this->executePreparedQuery($q, [$product_id], 's');
    }

    public function getGallery($product_id) {
        $q = "SELECT image_url FROM product_gallery WHERE product_id = ?";
        return $this->executePreparedQuery($q, [$product_id], 's');
    }

    public function getAttributes($product_id) {
        $q = "
            SELECT asets.name AS set_name, asets.type AS set_type,
                   attrs.display_value, attrs.value
            FROM product_attribute_sets pas
            LEFT JOIN attribute_sets asets ON pas.attribute_set_id = asets.id
            LEFT JOIN attributes attrs ON attrs.attribute_set_id = asets.id
            WHERE pas.product_id = ?
        ";
        return $this->executePreparedQuery($q, [$product_id], 's');
    }
}
