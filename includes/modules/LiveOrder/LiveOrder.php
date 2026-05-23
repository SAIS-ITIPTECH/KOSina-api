<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/LiveOrderQueryBuilder.php";
require_once __DIR__ . "/LiveOrderModel.php";

class LiveOrder implements Controller{
    private $queryBuilder;

    public function __construct(
        private $pdo,
        private $execution,
        private $id,
        private $role,
        private $datePage,
        private $pages
    ){}

    public function buildModel(){
        $model = new LiveOrderModel();
        $this->queryBuilder = new LiveOrderQueryBuilder($this->pdo, $this->execution, $model);
    }

    public function query(){
        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                $this->queryBuilder->get($this->datePage, $this->pages);
                break;

            case "PATCH":
                if (!$this->checkRole()) return;
                $this->queryBuilder->update($this->id);
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