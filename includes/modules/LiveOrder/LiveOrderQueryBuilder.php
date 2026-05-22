<?php
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Database/Execution.php";

class LiveOrderQueryBuilder{
    public function __construct(private $pdo, private $execution){}

    public function get(){
        $query = "
            SELECT 
                order_history.order_id, 
                product_list.name, 
                order_details.quantity,
                order_history.total_price,
                COUNT(order_details.detail_id) OVER (PARTITION BY order_history.order_id) AS item_count,
                (SELECT COUNT(DISTINCT order_id) FROM order_history WHERE served = 0 AND paid = 1) AS total_orders
            FROM order_history
            INNER JOIN order_details ON order_history.order_id = order_details.order_id
            INNER JOIN product_list ON order_details.product_id = product_list.product_id
            WHERE served = 0 AND paid = 1
            ORDER BY order_details.order_id DESC, order_details.detail_id ASC
        ";
        $stmt = $this->pdo->prepare($query);
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode($this->execution->getResults());
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