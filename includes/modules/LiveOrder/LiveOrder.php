<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/LiveOrderQueryBuilder.php";

class LiveOrder implements Controller{
    private $queryBuilder;

    public function __construct(
        private $pdo,
        private $execution,
        private $id
    ){}

    public function buildModel(){
        $this->queryBuilder = new LiveOrderQueryBuilder($this->pdo, $this->execution);
    }

    public function query(){
        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                $this->queryBuilder->get();
                break;

            case "PATCH":
                $this->queryBuilder->update($this->id);
                break;

            default:
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => strtoupper("Method Not Allowed!!!")]);
                return null;
        }
    }
}