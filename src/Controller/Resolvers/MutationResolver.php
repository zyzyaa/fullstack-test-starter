<?php
namespace App\Controller\Resolvers;

use App\models\Price;
use mysqli;
use Throwable;

class MutationResolver {
    public function __construct(private mysqli $db, private Price $priceModel) {}

    public function placeOrder(array $items): array {
        if (empty($items)) return ['success'=>false,'message'=>'No items'];

        $this->db->begin_transaction();
        try {
            $normalized = [];
            $total = 0.0;

            foreach ($items as $item) {
                $productId = $item['product_id'] ?? null;
                if (!$productId) throw new \RuntimeException('product_id is required');

                $priceRows = $this->priceModel->getByProductId($productId);
                $unitPrice = isset($priceRows[0]['amount']) ? (float)$priceRows[0]['amount'] : null;
                if ($unitPrice === null) throw new \RuntimeException("Price not found for product {$productId}");

                $qty   = (int)($item['item_quantity'] ?? 1);
                $attrs = (string)($item['item_attributes'] ?? '{}');
                $name  = (string)($item['item_name'] ?? 'Unknown');

                $total += $unitPrice * $qty;
                $normalized[] = ['name'=>$name,'qty'=>$qty,'attrs'=>$attrs,'price'=>$unitPrice];
            }

            $stmtOrder = $this->db->prepare("INSERT INTO orders (order_date, total_price) VALUES (NOW(), ?)");
            $stmtOrder->bind_param("d", $total);
            $stmtOrder->execute();
            $orderId = $this->db->insert_id;

            $stmt = $this->db->prepare(
                "INSERT INTO order_description (order_id, item_name, item_quantity, item_attributes, item_price) VALUES (?, ?, ?, ?, ?)"
            );
            foreach ($normalized as $n) {
                $stmt->bind_param("isisd", $orderId, $n['name'], $n['qty'], $n['attrs'], $n['price']);
                $stmt->execute();
            }

            $this->db->commit();
            return ['success'=>true,'order_id'=>$orderId,'message'=>'Order placed successfully','total_price'=>$total];
        } catch (Throwable $e) {
            $this->db->rollback();
            return ['success'=>false,'message'=>$e->getMessage()];
        }
    }
}
