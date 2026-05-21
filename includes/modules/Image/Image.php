<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/ImageModel.php";
require_once __DIR__ . "/ImageQueryBuilder.php";

class Image implements Controller{
    private $queryBuilder;

    public function __construct(
        private $pdo,
        private $execution,
        private $id,
        private $role
    ){}

    public function buildModel(){
        $model = new ImageModel($this->pdo);
        $this->queryBuilder = new ImageQueryBuilder($model, $this->pdo, $this->execution);
    }

    public function query(){
        if($_SERVER["REQUEST_METHOD"] === "OPTIONS") echo json_encode(["nigga" => "nigga", "lol" => $_SERVER["REQUEST_METHOD"]]);

        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                $this->queryBuilder->get();
                break;

            case "POST":
                $this->queryBuilder->post($this->id);
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