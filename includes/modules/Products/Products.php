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
    ){}

    public function buildModel(){
        $model = new ProductModel();
        $this->queryBuilder = new ProductQueryBuilder($model, $this->pdo, $this->execution);
    }

    public function query(){
        var_dump(gettype($this->id), $this->id);

        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                $this->queryBuilder->get($this->id);
                break;

            case "POST":
                $this->queryBuilder->post();
                break;

            case "PATCH":
                if(!$this->id){
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "This method needs an ID."]);
                    return;
                }
                $this->queryBuilder->update($this->id);
                break;

            case "DELETE":
                if(!$this->id){
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => "This method needs an ID."]);
                    return;
                }
                $this->queryBuilder->delete($this->id);
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => "Method not allowed."]);
                return;
        }
    }
}