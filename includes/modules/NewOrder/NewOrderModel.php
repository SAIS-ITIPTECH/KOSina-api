<?php

require_once __DIR__ . "/../../Validation/Validation.php";
require_once __DIR__ . "/../../Database/Database.php";

class NewOrderModel
{
    private string|null $dailySalesId  = null;
    private string|null $orderId       = null;
    private string|null $restaurantName = null;
    private string|null $paymentMethod = null;
    private array|null  $orders        = null;
    private float|null  $totalPrice    = null;
    private array       $userInput     = [];
    private Validation  $validator;

    public function __construct(private PDO $pdo, private DailySalesQueryBuilder $dailySalesQueryBuilder)
    {
        $this->validator = new Validation();
        $method          = $_SERVER["REQUEST_METHOD"] ?? "GET";
        if (in_array($method, ["POST", "PUT", "PATCH", "DELETE"])) {
            $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
        }
    }

    public function checkDailySales(): void
    {
        $latestId = $this->dailySalesQueryBuilder->getLatest();
        $today    = date("Ymd");

        if ($latestId !== $today) {
            $this->dailySalesId = $today;
            $this->dailySalesQueryBuilder->post($this->dailySalesId);
        } else {
            $this->dailySalesId = $latestId;
        }
    }

    public function validateFields(): bool
    {
        $restaurantName = $this->validateRestaurantName();
         if (!$restaurantName) { return false; }
        $paymentMethod  = $this->validatePaymentMethod();
        if (!$paymentMethod) { return false; }
       
        $this->restaurantName = $restaurantName;
        $this->paymentMethod  = $paymentMethod;
        $this->generateOrderId();
        return true;
    }

    public function generateOrderId(): void
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM order_history WHERE daily_sale_id = :setDailySalesId");
        $stmt->bindValue(":setDailySalesId", $this->dailySalesId);
        $stmt->execute();
        $lastIndex = $stmt->fetchColumn();

        $date      = date("Ymd");
        $autoIndex = str_pad($lastIndex + 1, 4, "0", STR_PAD_LEFT);
        $this->orderId = "{$date}-{$autoIndex}";
    }

    public function generateDetailId(int $count): string
    {
        $autoIndex = str_pad($count + 1, 4, "0", STR_PAD_LEFT);
        return "{$this->orderId}-{$autoIndex}";
    }

    public function validateOrders(): bool
    {
        if (!isset($this->userInput["orders"])) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => "THERE ARE NO ORDERS!"]);
            return false;
        }

        foreach ($this->userInput["orders"] as $index => $order) {
            if (!$this->validateProductId($order["productId"] ?? null)) {
                return false;
            }
            $this->userInput["orders"][$index]["price"] = $this->fetchPriceFromDb($order["productId"]);
            if (!$this->validateQuantity($order["quantity"] ?? null)) {
                return false;
            }
        }

        $this->totalPrice = $this->calculateTotalPrice();
        $this->orders     = $this->userInput["orders"];
        return true;
    }

    private function calculateTotalPrice(): float
    {
        $total = 0;
        foreach ($this->userInput["orders"] as $order) {
            $total += $order["price"] * $order["quantity"];
        }
        return $total;
    }

    private function validateRestaurantName(): mixed
    {
        $value = $this->validator->checkEmpty("RESTAURANT NAME", $this->userInput["restoName"] ?? null);
        if (isset($value)) {
            $value = $this->validator->checkSpecial("RESTAURANT NAME", $value, '/[^a-zA-Z0-9 _\-.]/');
        }
        return $value;
    }

    private function validatePaymentMethod(): mixed
    {
        $value = $this->validator->checkEmpty("PAYMENT METHOD", $this->userInput["paymentMethod"] ?? null);
        if (isset($value)) {
            $value = $this->validator->checkSpecial("PAYMENT METHOD", $value, '/[^a-zA-Z0-9 _\-.]/');
        }

        $allowedPayments = ["cash", "cashless"];
        if (!in_array($value, $allowedPayments)) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => "INVALID PAYMENT METHOD!"]);
            return false;
        }

        return $value;
    }

    private function validateProductId(mixed $value): mixed
    {
        $value = $this->validator->checkEmpty("PRODUCT ID", $value);
        if (isset($value)) {
            $value = $this->validator->checkSpecial("PRODUCT ID", $value, '/[^a-zA-Z0-9 _\-.]/');
        }
        return $value;
    }

    private function fetchPriceFromDb($productId): float
    {
        $stmt = $this->pdo->prepare("SELECT price FROM product_list WHERE product_id = :setId");
        $stmt->bindValue(":setId", $productId, PDO::PARAM_STR);
        $stmt->execute();
        return (float) $stmt->fetchAll(PDO::FETCH_ASSOC)[0]["price"];
    }

    private function validateQuantity(mixed $value): mixed
    {
        $value = $this->validator->checkEmpty("QUANTITY", $value);
        if (isset($value)) {
            $value = $this->validator->checkNumber("QUANTITY", $value);
        }
        return $value;
    }

    public function getDailySalesId(): string|null    { return $this->dailySalesId; }
    public function getRestaurantName(): string|null  { return $this->restaurantName; }
    public function getOrderId(): string|null         { return $this->orderId; }
    public function getOrders(): array|null           { return $this->orders; }
    public function getTotalPrice(): float|null       { return $this->totalPrice; }
    public function getPaymentMethod(): string|null   { return $this->paymentMethod; }
}
