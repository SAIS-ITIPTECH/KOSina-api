<?php
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Database/Execution.php";

class OrderServed{
    public function __construct(private $pdo, private $execution){}

    public function served($id){
        $query = "
            UPDATE order_history
            SET served = 1;
            WHERE order_id = :setid;
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $id, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper("ORDER COMPLETE")]);
    }
}