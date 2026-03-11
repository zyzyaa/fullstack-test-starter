<?php
declare(strict_types=1);

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: application/json; charset=UTF-8');

final class Db {
    public static function connect(): \mysqli {
        $db = new \mysqli('localhost', 'root', 'Scand!webTestShop', 'scandiweb_shop');
        if ($db->connect_error) {
            throw new \RuntimeException('DB connection failed: ' . $db->connect_error);
        }
        $db->set_charset('utf8mb4');
        return $db;
    }
}

final class OrderService {
    public function __construct(private \mysqli $db) {}

    public function create(array $items): array {
        if (empty($items)) {
            throw new \InvalidArgumentException('No items provided');
        }

        $this->db->begin_transaction();
        try {
            // orders
            $stmtOrder = $this->db->prepare('INSERT INTO orders (order_date) VALUES (NOW())');
            $stmtOrder->execute();
            $orderId = $this->db->insert_id;

            // order_description
            $stmtLine = $this->db->prepare(
                'INSERT INTO order_description (order_id, item_name, item_quantity, item_attributes, item_price) VALUES (?, ?, ?, ?, ?)'
            );

            foreach ($items as $item) {
                $name  = (string)($item['item_name'] ?? 'Unknown');
                $qty   = (int)($item['item_quantity'] ?? 1);
                $attrs = json_encode($item['item_attributes'] ?? [], JSON_UNESCAPED_UNICODE);
                $price = (float)($item['item_price'] ?? 0);

                $stmtLine->bind_param('isisd', $orderId, $name, $qty, $attrs, $price);
                $stmtLine->execute();
            }

            $this->db->commit();
            return ['success' => true, 'order_id' => $orderId];
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }
    }
}

final class RequestPayload {
    public static function read(): array {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            throw new \InvalidArgumentException('No input received');
        }
        $data = json_decode($raw, true);
        if (!is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }
        if (!isset($data['items']) || !is_array($data['items'])) {
            throw new \InvalidArgumentException('Field "items" is required');
        }
        return $data['items'];
    }
}

try {
    $items = RequestPayload::read();
    $service = new OrderService(Db::connect());
    $result = $service->create($items);
    echo json_encode($result);
} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
