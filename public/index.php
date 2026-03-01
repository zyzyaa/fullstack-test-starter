<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$mysqli = new mysqli("localhost", "root", "Scand!webTestShop", "scandiweb_shop");
if ($mysqli->connect_error) {
    die("Connect error: " . $mysqli->connect_error);
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../models/BaseModel.php';
require_once __DIR__ . '/../models/product.php';
require_once __DIR__ . '/../models/ClothesCategory.php';
require_once __DIR__ . '/../models/TechCategory.php';
require_once __DIR__ . '/../models/AllCategory.php';
require_once __DIR__ . '/../models/attribute.php';
require_once __DIR__ . '/../models/TextAttribute.php';
require_once __DIR__ . '/../models/SwatchAttribute.php';
require_once __DIR__ . '/../models/ProductAttribute.php';
require_once __DIR__ . '/../models/category.php';
require_once __DIR__ . '/../models/currency.php';
require_once __DIR__ . '/../models/price.php';
require_once __DIR__ . '/../models/gallery.php';
require_once __DIR__ . '/../models/AttributeSet.php';
require_once __DIR__ . '/../models/ProductAttributeSet.php';


use App\models\Category;
use App\models\Currency;
use App\models\ProductAttribute;
use App\models\Price;
use App\models\Gallery;
use App\models\AttributeSet;
use App\models\ProductAttributeSet;

$clothesModel = new \App\models\ClothesCategory($mysqli);
$techModel = new \App\models\TechCategory($mysqli);
$categoryModel = new Category($mysqli);
$currencyModel = new Currency($mysqli);
$attributeModel = new ProductAttribute($mysqli);
$priceModel = new Price($mysqli);
$galleryModel = new Gallery($mysqli);
$attributesetModel = new AttributeSet($mysqli);
$productattributesetModel = new ProductAttributeSet($mysqli);


$dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
    $r->post('/graphql', [App\Controller\GraphQL::class, 'handle']);
});

$routeInfo = $dispatcher->dispatch(
    $_SERVER['REQUEST_METHOD'],
    $_SERVER['REQUEST_URI']
);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::NOT_FOUND:
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        break;
    case FastRoute\Dispatcher::FOUND:
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];
        echo $handler($vars);
        break;
}