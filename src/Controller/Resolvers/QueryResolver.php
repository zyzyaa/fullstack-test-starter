<?php
namespace App\Controller\Resolvers;

use App\models\Category;
use App\models\Currency;
use App\models\AttributeSet;
use App\models\ProductAttributeSet;

class QueryResolver {
    public function __construct(
        private Category $category,
        private Currency $currency,
        private AttributeSet $attributeSet,
        private ProductAttributeSet $productAttributeSet,
        private ProductResolver $products
    ) {}

    public function categories(): array { return $this->category->getAll(); }
    public function currencies(): array { return $this->currency->getAll(); }
    public function attributeSets(): array { return $this->attributeSet->getAll(); }
    public function productAttributeSets(): array { return $this->productAttributeSet->getAll(); }
    public function products(): array { return $this->products->products(); }
    public function productsByCategory(string $category): array { return $this->products->productsByCategory($category); }
}
