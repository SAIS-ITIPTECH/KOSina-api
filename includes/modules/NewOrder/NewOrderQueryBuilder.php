<?php

require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../../services/PayMongo/CheckoutSession.php";

class NewOrderQueryBuilder{
     public function __construct(
        private $model, 
        private $pdo,
        private $execution,
        private $dailySalesQueryBuilder
        ){}

    public function post(){
        try {
            $this->pdo->beginTransaction();
            $this->model->checkDailySales();
            $good = $this->addNewOrder();
            if(!$good) {
                $this->pdo->rollBack();
                return null;
            }
            $good = $this->addOrderItems();
            if(!$good) {
                $this->pdo->rollBack();
                return null;
            }
            $this->updateTotalPrice();
            $this->updateDailySales();
            $this->pdo->commit();
            

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
        if ($this->model->getPaymentMethod() === "cashless") { 
            $this->checkout(); 
        } else {
            echo json_encode(["orderId" => $this->model->getOrderId()]);
        }

    }

    private function addNewOrder(){
        if(!$this->model->validateFields()) { return; }
        $query = "INSERT INTO order_history ( daily_sale_id, order_id, payment_method ) VALUES ( :setDailySalesId, :setOrderId, :setPaymentMethod );";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setDailySalesId",  $this->model->getDailySalesId(), PDO::PARAM_STR);
        $stmt->bindValue(":setOrderId",  $this->model->getOrderId(), PDO::PARAM_STR);
        $stmt->bindValue(":setPaymentMethod", $this->model->getPaymentMethod(), PDO::PARAM_STR);
        $this->execution->execute($stmt);
        return true;
    }

    private function addOrderItems(){
        if(!$this->model->validateOrders()) { return; }
        $query = [];
        foreach($this->model->getOrders() as $index => $order){
            $query[$index] = "INSERT INTO order_details ( detail_id, order_id, product_id, quantity ) VALUES ( :setDetailId{$index}, :setOrderId{$index}, :setProductId{$index}, :setQuantity{$index} );";
        }

        foreach($this->model->getOrders() as $index => $order){
            $stmt = $this->pdo->prepare($query[$index]);
            $stmt->bindValue(":setDetailId{$index}", $this->model->detailIdGenerator($index), PDO::PARAM_STR);
            $stmt->bindValue(":setOrderId{$index}", $this->model->getOrderId(), PDO::PARAM_STR);
            $stmt->bindValue(":setProductId{$index}", $order["productId"], PDO::PARAM_INT);
            $stmt->bindValue(":setQuantity{$index}", $order["quantity"], PDO::PARAM_INT);
            $this->execution->execute($stmt);
        }

        return true;
    }

    private function updateTotalPrice(){
        $query = "UPDATE order_history SET total_price = :setTotalPrice WHERE order_id = :setid";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setTotalPrice", $this->model->getTotalPrice(), PDO::PARAM_INT);
        $stmt->bindValue(":setid", $this->model->getOrderId(), PDO::PARAM_STR);
        $this->execution->execute($stmt);
    }

    private function updateDailySales(){
        $this->dailySalesQueryBuilder->update($this->model->getDailySalesId(), $this->model->getTotalPrice());
    }

    private function checkout(){
        $checkout = new CheckoutSession($this->model->getTotalPrice(), $this->model->getRestoname(), $this->model->getOrderId());
        $result = $checkout->createSession();
        echo json_encode([
            "id" => $result["data"]["id"],
            "url" => $result["data"]["attributes"]["checkout_url"],
            "orderId" => $this->model->getOrderId()
        ]);
    }

}