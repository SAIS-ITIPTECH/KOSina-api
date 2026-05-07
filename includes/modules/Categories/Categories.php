<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/CategoryModel.php";
require_once __DIR__ . "/CategoryQueryBuilder.php";

class Categories implements Controller{
    private $queryBuilder;

    public function __construct(
        private $id,
        private $pdo,
        private $execution

    ){}

    public function buildModel(){
        $model = new CategoryModel();
        $this->queryBuilder = new CategoryQueryBuilder($model, $this->pdo, $this->execution);
    }

    public function query(){
        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                $this->queryBuilder->get();
                break;

            case "POST":
                $this->queryBuilder->post();
                break;

            case "PATCH":
                if(!$this->id){
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => strtoupper("This method needs Id")]);
                    return;
                }
                $this->queryBuilder->update($this->id);
                break;

            case "DELETE":
                if(!$this->id){
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => strtoupper("This method needs Id")]);
                    return;
                }
                $this->queryBuilder->delete($this->id);
                break;

            default:
                http_response_code(404);
                echo json_encode(["status" => "error", "message" => strtoupper("Method Not Allowed!!!")]);
                return null;
        }
    }
}