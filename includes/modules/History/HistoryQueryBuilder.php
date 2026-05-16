<?php
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Database/Execution.php";

class HistoryQueryBuilder{
    private $msg;
    
    public function __construct(private $model, private $pdo, private $execution){}

    public function get($datePage = false, $page = false){
        
        if (!$datePage && !$page) {
            $query = "
                SELECT order_history.*, daily_sales.daily_sale_id
                FROM order_history
                LEFT JOIN daily_sales
                ON order_history.daily_sale_id = daily_sales.daily_sale_id
                ORDER BY order_id DESC
            ";

            $stmt = $this->pdo->prepare($query);
            $this->execution->execute($stmt);
            $result = $this->execution->getResults();
            http_response_code(200);
            echo json_encode($result);

        } else {
            var_dump($datePage, $page);
            if(!$this->model->datePageValidator($datePage)){return;}
            if(!$this->model->pageValidator($page)){return;}

            $query =  "
                SELECT order_history.*, daily_sales.daily_sale_id
                FROM order_history
                LEFT JOIN daily_sales
                ON order_history.daily_sale_id = daily_sales.daily_sale_id
                WHERE DATE(order_date) = DATE(:setDateLimit)
                ORDER BY order_id DESC
                LIMIT 50 offset :setLimit;
            " ;

            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":setDateLimit", $datePage, PDO::PARAM_STR);
            $stmt->bindValue(":setLimit", $page, PDO::PARAM_INT);
            $this->execution->execute($stmt);
            $result = $this->execution->getResults();
            echo "lols";
            http_response_code(200);
            echo json_encode($result);
        }
    }

    public function update($id){
        if(!$this->model->idValidator($id)){return;}

        $query = "
            UPDATE order_history
            SET total_price = :setTotalPrice, paid = :setPaid, payment_method = :setPaymentMethod
            WHERE order_id = :setid;
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $id, PDO::PARAM_STR);
        $this->superBind($stmt, "HAS BEEN UPDATED");
    }

    public function delete($id){
        if(!$this->model->validateId($id)) return;

        $query = "DELETE FROM order_history WHERE order_id = :setId";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setId", $id, PDO::PARAM_INT);
        $this->msg = "{$id} has been deleted.";
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }

    private function superBind($stmt, $secondMessage){
        if(!$this->model->validateFields()) return;

        $stmt->bindValue(":setTotalPrice", $this->model->getTotalPrice(), PDO::PARAM_INT);
        $stmt->bindValue(":setPaid", $this->model->getPaid(), PDO::PARAM_INT);
        $stmt->bindValue(":setPaymentMethod", $this->model->getPaymentMethod(), PDO::PARAM_INT);

        $this->msg = "Order detail {$secondMessage}";
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }
}