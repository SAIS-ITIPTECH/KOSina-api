<?php

require_once __DIR__ . "/../../Database/CredentialsGraber.php";
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Validation/Validation.php";

class CountTotal{
    private $query;
    private $tableMap = [
        "history" => "
            SELECT COUNT(*) AS total 
            FROM order_history
            WHERE DATE(order_date) = DATE(:setDateLimit)",

        "details" => "
            SELECT COUNT(*) AS total
            FROM order_details
            LEFT JOIN order_history
            ON order_history.order_id = order_details.order_id
            WHERE DATE(order_date) = DATE(:setDateLimit)
        ",
            
        "sales" =>  "daily_sale"
    ];

    public function __construct(
        $table,
        private $pdo,
        private $execution
    ) {
        $this->query = $this->tableMap[$table] ?? false;
    }

    public function count($datePage){
        if (!$this->query) { return; }
        if (!$this->validateDatePage($datePage)) { return; }

        $stmt = $this->pdo->prepare($this->query);
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