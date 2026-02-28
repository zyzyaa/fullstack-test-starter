<?php
namespace App\models;

abstract class ProductRelatedModel extends BaseModel
{
    abstract public function getByProductId($productId);
}