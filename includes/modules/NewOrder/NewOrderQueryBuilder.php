<?php

require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../../services/PayMongo/CheckoutSession.php";

class NewOrderQueryBuilder
{
    public function __construct(
        private NewOrderModel           $model,
        private PDO                     $pdo,
        private Execution               $execution,
        private DailySalesQueryBuilder  $dailySalesQueryBuilder
    ) {}

    public function post(): void
    {
        try {
            $this->pdo->beginTransaction();
            $this->model->checkDailySales();

            if (!$this->insertOrder() || !$this->insertOrderItems()) {
                $this->pdo->rollBack();
                return;
            }

            $this->updateTotalPrice();
            $this->pdo->commit();

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        if ($this->model->getPaymentMethod() === "cashless") {
            $this->initiateCheckout();
        } else {
            echo json_encode(["orderId" => $this->model->getOrderId()]);
        }
    }

    private function insertOrder(): bool
    {
        if (!$this->model->validateFields()) {
            return false;
        }
        $stmt = $this->pdo->prepare("
            INSERT INTO order_history (daily_sale_id, order_id, payment_method)
            VALUES (:setDailySalesId, :setOrderId, :setPaymentMethod)
        ");
        $stmt->bindValue(":setDailySalesId",  $this->model->getDailySalesId(),  PDO::PARAM_STR);
        $stmt->bindValue(":setOrderId",        $this->model->getOrderId(),        PDO::PARAM_STR);
        $stmt->bindValue(":setPaymentMethod",  $this->model->getPaymentMethod(), PDO::PARAM_STR);
        $this->execution->execute($stmt);
        return true;
    }

    private function insertOrderItems(): bool
    {
        if (!$this->model->validateOrders()) {
            return false;
        }
        foreach ($this->model->getOrders() as $index => $order) {
            $stmt = $this->pdo->prepare("
                INSERT INTO order_details (detail_id, order_id, product_id, quantity)
                VALUES (:setDetailId, :setOrderId, :setProductId, :setQuantity)
            ");
            $stmt->bindValue(":setDetailId",  $this->model->generateDetailId($index), PDO::PARAM_STR);
            $stmt->bindValue(":setOrderId",   $this->model->getOrderId(),              PDO::PARAM_STR);
            $stmt->bindValue(":setProductId", $order["productId"],                    PDO::PARAM_STR);
            $stmt->bindValue(":setQuantity",  $order["quantity"],                     PDO::PARAM_INT);
            $this->execution->execute($stmt);
        }
        return true;
    }

    private function updateTotalPrice(): void
    {
        $stmt = $this->pdo->prepare("UPDATE order_history SET total_price = :setTotalPrice WHERE order_id = :setId");
        $stmt->bindValue(":setTotalPrice", $this->model->getTotalPrice(), PDO::PARAM_INT);
        $stmt->bindValue(":setId",         $this->model->getOrderId(),    PDO::PARAM_STR);
        $this->execution->execute($stmt);
    }

    private function initiateCheckout(): void
    {
        $checkout = new CheckoutSession(
            $this->model->getTotalPrice(),
            $this->model->getRestaurantName(),
            $this->model->getOrderId()
        );
        $result = $checkout->createSession();
        echo json_encode([
            "id"      => $result["data"]["id"],
            "url"     => $result["data"]["attributes"]["checkout_url"],
            "orderId" => $this->model->getOrderId(),
        ]);
    }
}
