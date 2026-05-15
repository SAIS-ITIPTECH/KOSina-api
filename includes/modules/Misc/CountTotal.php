<?php

require_once __DIR__ . "/../../Database/CredentialsGraber.php";
require_once __DIR__ . "/../../Database/Database.php";

class CountTotal{
    private $id;
    public function __construct(
        $id,
        private $pdo,
        private $execution
    ) {
        $this->validateTable($id);
    }

    public function count(){
        if (!$this->id) { return; }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM '$this->id'");
        $this->execution->execute($stmt);
        echo json_encode($this->execution->getResult());
    }

    public function validateTable($id){
        $allowed = ["order_history", "order_details", "daily_sale"];
        if (in_array($allowed, $id)) {
            $this->id = $id;
        }  else {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("The table name is invalid.")]);
            $this->id = false;
        }
    }
}