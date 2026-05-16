<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "/HistoryModel.php";
require_once __DIR__ . "/HistoryQueryBuilder.php";

class History implements Controller {
    private $queryBuilder;

    public function __construct(
        private $pdo,
        private $execution,
        private $id,
        private $datePage,
        private $page
    ){} 

    public function buildModel(){
        var_dump("h", $this->datePage, $this->page);
        $model = new HistoryModel();
        $this->queryBuilder = new HistoryQueryBuilder($model, $this->pdo, $this->execution);
    }

    public function query(){
        switch($_SERVER["REQUEST_METHOD"]){
            case "GET":
                $this->queryBuilder->get($this->datePage, $this->page);
                break;

            case "PATCH":
                if(!$this->id){
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => strtoupper("This method needs an ID.")]);
                    return;
                }
                $this->queryBuilder->update($this->id);
                break;

            case "DELETE":
                if(!$this->id){
                    http_response_code(400);
                    echo json_encode(["status" => "error", "message" => strtoupper("This method needs an ID.")]);
                    return;
                }
                $this->queryBuilder->delete($this->id);
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => strtoupper("Method not allowed.")]);
                return;
        }
    }
}
