<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/ProductModel.php";
require_once __DIR__ . "/ProductQueryBuilder.php";

class Products implements Controller {
    private $queryBuilder;

    public function __construct(
        private $pdo,
        private $execution,
        private $id,
        private $role
    ){}

    public function buildModel(){
        $model = new ProductModel();
        $this->queryBuilder = new ProductQueryBuilder($model, $this->pdo, $this->execution);
    }

    public function query(){
        error_log($this->role);
        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                error_log("get");
                
                $this->queryBuilder->get($this->id);
                break;

            case "POST":
                error_log("post");

                if (!$this->checkRole()) return;
                $this->queryBuilder->post();
                break;

            case "PATCH":
                error_log("update");

                if (!$this->checkRole()) return;
                if (!$this->checkId()) return;
                $this->queryBuilder->update($this->id);
                break;

            case "DELETE":
                error_log("delete");

                if (!$this->checkRole()) return;
                if (!$this->checkId()) return;
                $this->queryBuilder->delete($this->id);
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Method not allowed."]);
                return;
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