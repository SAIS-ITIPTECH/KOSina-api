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
        $this->table = $this->tableMap[$table] ?? false;
    }

    public function count($datePage){
        var_dump(1, $datePage);
        if (!$this->table) { return; }
        if (!$this->validateDatePage($datePage)) { return; }

        var_dump(4, $datePage);
        $stmt = $this->pdo->prepare("SELECT COUNT(*) AS total FROM $this->table WHERE DATE(order_date) = DATE(:setDateLimit)");
        $stmt->bindValue(":setDateLimit", $datePage, PDO::PARAM_STR);
        var_dump(5, $datePage);
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        var_dump(6, $datePage);
        echo json_encode($result[0]);
    }

    private function validateDatePage($datePage){
        $validation = new Validation();
        var_dump(2, $datePage);
        if ($validation->checkEmpty("DATE PAGE", $datePage) === null) { return false; }
        if ($validation->checkSpecial("DATE PAGE", $datePage, '/[^a-zA-Z0-9\-]/') === null) { return false; }
        var_dump(3, $datePage);
    }
}