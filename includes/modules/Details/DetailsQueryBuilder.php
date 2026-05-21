<?php
    require_once __DIR__ . "/../../Database/Execution.php";
    require_once __DIR__ . "/../../Database/Database.php";
    

    class DetailsQueryBuilder{
        private $msg;

        public function __construct(private $model, private $pdo, private $execution){}

        public function get($datePage, $page){
            if(!$this->model->datePageValidator($datePage)){return;}
            if(!$this->model->pageValidator($page)){return;}

            $query =  "
                SELECT order_details.*, product_list.name, order_history.order_date
                FROM order_details
                LEFT JOIN product_list
                ON product_list.product_id = order_details.product_id
                LEFT JOIN order_history
                ON order_details.order_id = order_history.order_id
                WHERE DATE(order_date) = DATE(:setDateLimit)
                ORDER BY order_id DESC
                LIMIT 50 offset :setLimit;
            ";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":setDateLimit", $datePage, PDO::PARAM_STR);
            $stmt->bindValue(":setLimit", $page, PDO::PARAM_INT);
            $this->execution->execute($stmt);
            $result = $this->execution->getResults();
            http_response_code(200);
            echo json_encode($result);
        }
 
        public function update($id){
            if(!$this->model->validateId($id)){return;}

            $query = "UPDATE order_details SET order_id = :setOrderId, product_id = :setProductId, quantity = :setQuantity WHERE items_id = :setid";
            $stmt = $this->pdo->prepare($query); 
            $stmt->bindValue(":setid", $id, PDO::PARAM_STR);
            $this->superBind($stmt, "HAS BEEN UPDATED");
        }

        public function delete($id){
            if(!$this->model->validateId($id)) return;

            $query = "DELETE FROM order_details WHERE items_id = :setId";
            $stmt = $this->pdo->prepare($query);
            $stmt->bindValue(":setId", $id, PDO::PARAM_INT);
            $this->msg = "{$id} has been deleted.";
            $this->execution->execute($stmt);
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
        }

        private function superBind($stmt, $secondMessage){
            if(!$this->model->validateFields()) return;

            $stmt->bindValue(":setOrderId", $this->model->getOrderId(), PDO::PARAM_INT);
            $stmt->bindValue(":setProductId", $this->model->getProductId(), PDO::PARAM_INT);
            $stmt->bindValue(":setQuantity", $this->model->getQuantity(), PDO::PARAM_INT);

            $this->msg = "Order detail {$secondMessage}";
            $this->execution->execute($stmt);
            http_response_code(200);
            echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
        }

    }