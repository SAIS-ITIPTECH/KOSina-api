<?php

require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/NewOrderModel.php";
require_once __DIR__ . "/NewOrderQueryBuilder.php";
require_once __DIR__ . "/../../modules/DailySales/DailySalesQueryBuilder.php";

class NewOrder implements Controller
{
    private NewOrderQueryBuilder $queryBuilder;

    public function __construct(
        private PDO       $pdo,
        private Execution $execution,
        private ?string   $id,
        private string    $role
    ) {}

    public function buildModel(): void
    {
        $dailySalesQueryBuilder = new DailySalesQueryBuilder($this->pdo, $this->execution);
        $model                  = new NewOrderModel($this->pdo, $dailySalesQueryBuilder);
        $this->queryBuilder     = new NewOrderQueryBuilder($model, $this->pdo, $this->execution, $dailySalesQueryBuilder);
    }

    public function query(): void
    {
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "POST":
                if (!$this->checkRole()) return;
                $this->queryBuilder->post();
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "METHOD NOT ALLOWED"]);
        }
    }

    private function checkRole(): bool
    {
        if ($this->role !== "kiosk") {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "ONLY KIOSK CAN ADD NEW ORDER!"]);
            return false;
        }
        return true;
    }
}
