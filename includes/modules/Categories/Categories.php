<?php

require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/CategoryModel.php";
require_once __DIR__ . "/CategoryQueryBuilder.php";

class Categories implements Controller
{
    private CategoryQueryBuilder $queryBuilder;

    public function __construct(
        private PDO       $pdo,
        private Execution $execution,
        private ?string   $id,
        private string    $role
    ) {}

    public function buildModel(): void
    {
        $model              = new CategoryModel();
        $this->queryBuilder = new CategoryQueryBuilder($model, $this->pdo, $this->execution);
    }

    public function query(): void
    {
        switch ($_SERVER["REQUEST_METHOD"]) {
            case "GET":
                $this->queryBuilder->get();
                break;

            case "POST":
                if (!$this->checkRole()) return;
                $this->queryBuilder->post();
                break;

            case "PATCH":
                if (!$this->checkRole()) return;
                if (!$this->checkId()) return;
                $this->queryBuilder->update($this->id);
                break;

            case "DELETE":
                if (!$this->checkRole()) return;
                if (!$this->checkId()) return;
                $this->queryBuilder->delete($this->id);
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "METHOD NOT ALLOWED"]);
        }
    }

    private function checkRole(): bool
    {
        if ($this->role !== "admin") {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "ONLY ADMIN CAN MODIFY KIOSK DATA!"]);
            return false;
        }
        return true;
    }

    private function checkId(): bool
    {
        if (!$this->id) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "THIS METHOD NEEDS AN ID!"]);
            return false;
        }
        return true;
    }
}
