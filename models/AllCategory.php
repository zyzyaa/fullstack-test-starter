<?php
namespace App\models;

class AllCategory
{
    public function __construct(
        private ClothesCategory $clothes,
        private TechCategory $tech
    ) {}

    public function getAll(): array {
        return array_merge($this->clothes->getAll(), $this->tech->getAll());
    }
}
