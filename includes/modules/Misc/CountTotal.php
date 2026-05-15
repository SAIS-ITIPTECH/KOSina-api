<?php

require_once __DIR__ . "/../../Database/CredentialsGraber.php";
require_once __DIR__ . "/../../Database/Database.php";

class CountTotal{
    private $table;
    private $tableMap = [
        "categories" => "order_history",
        "products" => "order_details",
        "sales" =>  "daily_sale"
    ];

    public function __construct(
        $table,
        private $pdo,
        private $execution
    ) {
        $this->table = $this->tableMap[$table] ?? false;
    }

    public function count(){
        if (!$this->table) { return; }
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM $this->table");
        $this->execution->execute($stmt);
        echo json_encode($this->execution->getResult());
    }

}