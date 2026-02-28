<?php
namespace App\Controller\Resolvers;

use App\models\ClothesCategory;
use App\models\TechCategory;
use App\models\AllCategory;
use App\models\Price;
use App\models\Gallery;
use App\models\ProductAttribute;

class ProductResolver {
    public function __construct(
        private ClothesCategory $clothes,
        private TechCategory $tech,
        private AllCategory $all,
        private Price $priceModel,
        private Gallery $galleryModel,
        private ProductAttribute $attributeModel
    ) {}

    public function products(): array {
        return array_merge($this->clothes->getAll(), $this->tech->getAll());
    }

    public function productsByCategory(string $category): array {
        $map = [
            'all' => fn() => $this->all->getAll(),
            'clothes' => fn() => $this->clothes->getAll(),
            'tech' => fn() => $this->tech->getAll(),
        ];
        $callable = $map[strtolower($category)] ?? null;
        if (!$callable) {
            throw new \RuntimeException("Unknown category: $category");
        }
        return $callable();
    }

    public function gallery(array $product): array {
        return $this->galleryModel->getByProductId($product['product_id']);
    }

    public function prices(array $product): array {
        return $this->priceModel->getByProductId($product['product_id']);
    }

    public function attributes(array $product): array {
        return $this->attributeModel->getByProductId($product['product_id']);
    }
}
