<?php

require_once __DIR__ . "/../../Database/CredentialsGrabber.php";
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Validation/Validation.php";

class CountTotal
{
    private string|false $query;
    private int $totalCount;
    private int $totalPages;

    private array $tableMap = [
        "history" => "
            SELECT COUNT(*) AS total
            FROM order_history
            WHERE DATE(order_date) = DATE(:setDateLimit)
        ",
        "details" => "
            SELECT COUNT(*) AS total
            FROM order_details
            LEFT JOIN order_history ON order_history.order_id = order_details.order_id
            WHERE DATE(order_date) = DATE(:setDateLimit)
        ",
        "sales" => "daily_sale",
        "liveorder" => "
            SELECT COUNT(*) AS total
            FROM order_history
            WHERE DATE(order_date) = DATE(:setDateLimit) AND served = 0 AND paid = 1
        "
    ];

    public function __construct(
        private string    $table,
        private PDO       $pdo,
        private Execution $execution
    ) {
        $this->query = $this->tableMap[$this->table] ?? false;
    }

    public function count(string|null $datePage): mixed
    {
        if (!$this->query) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("INVALID TABLE NAME")]);
            return false;
        }

        if (!$this->validateDatePage($datePage)) { return false; }

        $stmt = $this->pdo->prepare($this->query);
        $stmt->bindValue(":setDateLimit", $datePage, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        $this->totalCount = (int) ($this->execution->getResults())[0]["total"];
        $this->totalPages = ceil(($this->totalCount) / 50);

        return true;
    }

    public function sendBack(): void
    {
        echo json_encode([
            "total" => $this->totalCount,
            "totalPages" => $this->totalPages
        ]);
    }

    public function getCount(): int
    {
        return $this->totalCount;
    }

    private function validateDatePage(string|null $datePage): bool
    {
        $validation = new Validation();
        if ($validation->checkEmpty("DATE PAGE", $datePage) === null)                               { return false; }
        if ($validation->checkSpecial("DATE PAGE", $datePage, '/[^a-zA-Z0-9\-]/') === null)        { return false; }
        return true;
    }
}
