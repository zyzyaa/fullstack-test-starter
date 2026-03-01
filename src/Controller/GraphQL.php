<?php
namespace App\Controller;

use GraphQL\GraphQL as GraphQLBase;
use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use GraphQL\Type\Schema;
use RuntimeException;
use Throwable;

use App\Controller\Resolvers\ProductResolver;
use App\Controller\Resolvers\QueryResolver;
use App\Controller\Resolvers\MutationResolver;

use App\models\Category;
use App\models\AllCategory;
use App\models\ClothesCategory;
use App\models\TechCategory;
use App\models\Currency;
use App\models\ProductAttribute;
use App\models\Price;
use App\models\Gallery;
use App\models\AttributeSet;
use App\models\ProductAttributeSet;
use App\models\TextAttribute;
use App\models\SwatchAttribute;


class GraphQL {
    static public function handle() {
        try {
            $mysqli = new \mysqli("localhost", "root", "Te5t!", "scandiweb_shop");
            if ($mysqli->connect_error) {
                throw new RuntimeException("DB connect error: " . $mysqli->connect_error);
            }
            $mysqli->set_charset('utf8mb4');

            $categoryModel = new Category($mysqli);
            $clothesModel = new ClothesCategory($mysqli);
            $techModel = new TechCategory($mysqli);
            $currencyModel = new Currency($mysqli);
            $attributeModel = new ProductAttribute($mysqli, [
                new TextAttribute($mysqli),
                new SwatchAttribute($mysqli),
            ]);
            $priceModel = new Price($mysqli);
            $galleryModel = new Gallery($mysqli);
            $attributesetModel = new AttributeSet($mysqli);
            $productattributesetModel = new ProductAttributeSet($mysqli);
            $allCategory = new AllCategory($clothesModel, $techModel);

            $productResolver = new ProductResolver(
                $clothesModel,
                $techModel,
                $allCategory,
                $priceModel,
                $galleryModel,
                $attributeModel
            );

            $queryResolver = new QueryResolver(
                $categoryModel,
                $currencyModel,
                $attributesetModel,
                $productattributesetModel,
                $productResolver
            );

            $mutationResolver = new MutationResolver($mysqli, $priceModel);


            // ------------------------- TYPES -------------------------
            $categoryType = new ObjectType([
                'name' => 'Category',
                'fields' => [
                    'id' => Type::int(),
                    'name' => Type::string(),
                ],
            ]);

            $attributeType = new ObjectType([
                'name' => 'Attribute',
                'fields' => [
                    'attribute_id' => Type::int(),
                    'value' => Type::string(),
                    'display_value' => Type::string(),
                    'set_name' => Type::string(),
                    'set_type' => Type::string(),
                ],
            ]);

            $priceType = new ObjectType([
                'name' => 'Price',
                'fields' => [
                    'amount' => Type::float(),
                    'label' => Type::string(),
                    'symbol' => Type::string(),
                ],
            ]);

            $galleryType = new ObjectType([
                'name' => 'GalleryItem',
                'fields' => [
                    'image_url' => Type::string(),
                ],
            ]);

            $productType = new ObjectType([
                'name' => 'Product',
                'fields' => [
                    'product_id' => Type::string(),
                    'product_name' => Type::string(),
                    'description' => Type::string(),
                    'in_stock' => Type::int(),
                    'category_name' => Type::string(),
                    'brand' => Type::string(),
                    'gallery' => [
                        'type' => Type::listOf($galleryType),
                        'resolve' => [$productResolver, 'gallery'],
                    ],
                    'prices' => [
                        'type' => Type::listOf($priceType),
                        'resolve' => [$productResolver, 'prices'],
                    ],
                    'attributes' => [
                        'type' => Type::listOf($attributeType),
                        'resolve' => [$productResolver, 'attributes'],
                    ],
                ],
            ]);

            $productAttributeSetType = new ObjectType([
                'name' => 'ProductAttributeSet',
                'fields' => [
                    'id' => Type::int(),
                    'name' => Type::string(),
                ],
            ]);

            $currencyType = new ObjectType([
                'name' => 'Currency',
                'fields' => [
                    'id' => Type::int(),
                    'name' => Type::string(),
                ],
            ]);

            $attributeSetType = new ObjectType([
                'name' => 'AttributeSet',
                'fields' => [
                    'id' => Type::int(),
                    'name' => Type::string(),
                ],
            ]);

            // ------------------------- QUERY -------------------------
            $queryType = new ObjectType([
                'name' => 'Query',
                'fields' => [
                    'categories' => [
                        'type' => Type::listOf($categoryType),
                        'resolve' => [$queryResolver, 'categories'],
                    ],
                    'products' => [
                        'type' => Type::listOf($productType),
                        'resolve' => [$productResolver, 'products'],
                    ],
                    'productsByCategory' => [
                        'type' => Type::listOf($productType),
                        'args' => [
                            'categoryName' => Type::nonNull(Type::string())
                        ],
                        'resolve' => fn($root, $args) => $productResolver->productsByCategory($args['categoryName']),
                    ],
                    'currencies' => [
                        'type' => Type::listOf($currencyType),
                        'resolve' => [$queryResolver, 'currencies'],
                    ],
                    'attribute_sets' => [
                        'type' => Type::listOf($attributeSetType),
                        'resolve' => [$queryResolver, 'attributeSets'],
                    ],
                    'product_attribute_sets' => [
                        'type' => Type::listOf($productAttributeSetType),
                        'resolve' => [$queryResolver, 'productAttributeSets'],
                    ],
                ],
            ]);

            // ------------------------- MUTATION -------------------------
            
            $orderItemInputType = new InputObjectType([
                'name' => 'OrderItemInput',
                'fields' => [
                    'product_id' => Type::nonNull(Type::string()),
                    'item_name' => Type::nonNull(Type::string()),
                    'item_quantity' => Type::nonNull(Type::int()),
                    'item_attributes' => Type::string(),
                    'item_price' => Type::float(),
                ],
            ]);

            $placeOrderResponseType = new ObjectType([
                'name' => 'PlaceOrderResponse',
                'fields' => [
                    'success' => Type::nonNull(Type::boolean()),
                    'order_id' => Type::id(),
                    'message' => Type::string(),
                    'total_price' => Type::float(),
                ],
            ]);

            $mutationType = new ObjectType([
                'name' => 'Mutation',
                'fields' => [
                    'placeOrder' => [
                        'type' => $placeOrderResponseType,
                        'args' => [
                            'items' => Type::nonNull(Type::listOf(Type::nonNull($orderItemInputType))),
                        ],
                        'resolve' => fn($root, $args) => $mutationResolver->placeOrder($args['items']),
                    ],
                ],
            ]);


            // ------------------------- SCHEMA -------------------------
            $schema = new Schema([
                'query' => $queryType,
                'mutation' => $mutationType,
            ]);


            
            $rawInput = file_get_contents('php://input');
            if ($rawInput === false) throw new RuntimeException('Failed to get php://input');

            $input = json_decode($rawInput, true);
            $query = $input['query'] ?? '';
            $variableValues = $input['variables'] ?? null;

            $result = GraphQLBase::executeQuery($schema, $query, null, null, $variableValues);
            $output = $result->toArray();

        } catch (Throwable $e) {
            $output = ['error' => ['message' => $e->getMessage()]];
        }

        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($output);
    }
}
