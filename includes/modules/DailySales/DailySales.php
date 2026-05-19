<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/DailySalesQueryBuilder.php";

class DailySales implements Controller{
    private $queryBuilder;

    public function __construct(
        private $pdo,
        private $execution,
        private $id,
        private $role
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
                if (!$this->checkRole()) return;
                if (!$this->checkId()) return;
                $this->queryBuilder->delete($this->id);
                break;

            default:
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => strtoupper("Method Not Allowed!!!")]);
                return null;
        }
    }

    private function checkRole(){
        if($this->role != "admin") {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => strtoupper("ONLY ADMIN CAN MODIFY KIOSK DATA!")]);
            return false;
        }
        return true;
    }

    private function checkId(){
        if(!$this->id){
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => strtoupper("THIS METHOD NEEDS AN ID!")]);
            return false;
        }
        return true;
    }
}