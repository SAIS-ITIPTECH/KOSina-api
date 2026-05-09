<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/DailySalesQueryBuilder.php";

class DailySales implements Controller{
    private $queryBuilder;

    public function __construct(
        private $pdo,
        private $execution,
        private $id
    ){}

    public function buildModel(){
        $this->queryBuilder = new DailySalesQueryBuilder($this->pdo, $this->execution);
    }

    public function query(){
        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                $this->queryBuilder->get();
                break;

            case "DELETE":
                $this->queryBuilder->delete($this->id);
                break;

            default:
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => strtoupper("Method Not Allowed!!!")]);
                return null;
        }
    }
}