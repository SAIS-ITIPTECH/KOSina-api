<?php
require_once "includes/Controller/Controller.php";
require_once "ImageModel.php";
require_once "ImageQueryBuilder.php";

class Image implements Controller{
    private $queryBuilder;

    public function __construct(
        private $id,
        private $pdo,
        private $execution
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