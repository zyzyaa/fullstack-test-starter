<?php
namespace App\models;

class ProductAttribute extends BaseModel {
    private array $providers;

    public function __construct($mysqli, array $providers = []) {
        parent::__construct($mysqli);
        $this->providers = $providers ?: [
            new TextAttribute($mysqli),
            new SwatchAttribute($mysqli),
        ];
    }

    public function getByProductId($productId): array {
        $result = [];
        foreach ($this->providers as $provider) {
            $result = array_merge($result, $provider->getByProductId($productId));
        }
        return $result;
    }
}