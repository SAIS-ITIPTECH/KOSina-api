<?php

require_once __DIR__ . "/../../Database/CredentialsGraber.php";
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Validation/Validation.php";

class CountTotal{
    private $table;
    private $tableMap = [
        "history" => "order_history",
        "details" => "order_details",
        "sales" =>  "daily_sale"
    ];

    public function __construct(
        $table,
        private $pdo,
        private $execution
    ) {
        var_dump(5);
        $this->table = $this->tableMap[$table] ?? false;
    }

    public function count($datePage){
        var_dump(6, $this->table);
        if (!$this->table) { return; }
        if (!$this->validateDatePage($datePage)) { return; }

        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM $this->table WHERE DATE(order_date) = DATE(:setDateLimit)");
        $stmt->bindValue(":setDateLimit", $datePage, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        echo json_encode($result[0]);
    }

    private function validateDatePage($datePage){
        $validation = new Validation();
        if ($validation->checkEmpty("DATE PAGE", $datePage) === null) { return false; }
        if ($validation->checkSpecial("DATE PAGE", $datePage, '/[^a-zA-Z0-9\-]/') === null) { return false; }
        return true;
    }
}