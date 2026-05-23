<?php
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../Misc/CountTotal.php";

class LiveOrderQueryBuilder{
    public function __construct(private $pdo, private $execution, private $model){}

    public function get($datePage, $total){
        if(!$this->model->datePageValidator($datePage)){return;}

        // VALIDATE INPUTS
        if(!$this->model->datePageValidator($datePage)){return;}

        // GET THE TOTAL COUNT FROM DB
        $count = new CountTotal("liveorder", $this->pdo, $this->execution);
        $success = $count->count($datePage);
        if (!$success) {return;}
        $dbTotal = $count->getCount();

        // ASSIGN AND VALIDATE TOTAL
        if(!$this->model->totalValidator($total)){return;}

        // CHECK IF THERE IS NEW DATA
        if ($total >= $dbTotal) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => strtoupper("NO NEW DATA."),
                "total" => $dbTotal
            ]);
            return;
        }
       
        $query = "
            SELECT
                order_history.order_id,
                product_list.name,
                order_details.quantity,
                order_history.total_price
            FROM (
                SELECT order_id FROM order_history
                WHERE served = 0 AND paid = 1 AND DATE(order_date) = DATE(:setDateLimit)
                ORDER BY order_id DESC
                LIMIT 50 OFFSET 0
            ) AS limited_orders
            INNER JOIN order_history ON limited_orders.order_id = order_history.order_id
            INNER JOIN order_details ON order_history.order_id = order_details.order_id
            INNER JOIN product_list ON order_details.product_id = product_list.product_id
            ORDER BY order_history.order_id ASC, order_details.detail_id ASC;
        ";
        
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setDateLimit", $datePage, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode([
            "total" => $dbTotal,
            "result" => $this->execution->getResults()
        ]);
    }

    public function update($id){
        $query = "
            UPDATE order_history
            SET served = 1
            WHERE order_id = :setid;
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $id, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper("ORDER PREPARED")]);
    }
}