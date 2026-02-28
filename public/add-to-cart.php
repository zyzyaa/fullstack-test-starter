<?php
declare(strict_types=1);

session_start();
header('Content-Type: application/json; charset=UTF-8');

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
        $productId = $data['product_id'] ?? null;
        if (!$productId) {
            throw new \InvalidArgumentException('No product ID');
        }
        $attrs = $data['attributes'] ?? [];
        return ['product_id' => $productId, 'attributes' => $attrs];
    }
}

final class CartService {
    public function __construct() {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }
    public function add(string $productId, array $attributes): array {
        $_SESSION['cart'][] = [
            'product_id' => $productId,
            'attributes' => $attributes,
        ];
        return $_SESSION['cart'];
    }
}

try {
    $payload = RequestPayload::read();
    $cart = (new CartService())->add($payload['product_id'], $payload['attributes']);
    echo json_encode(['success' => true, 'items' => $cart]);
} catch (\Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
