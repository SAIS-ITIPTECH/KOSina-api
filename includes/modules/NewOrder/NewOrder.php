<?php
require_once __DIR__ . "/../../Controller/Controller.php";
require_once __DIR__ . "NewOrderModel.php";
require_once __DIR__ . "NewOrderQueryBuilder.php";
require_once __DIR__ . "/../../modules/DailySales/DailySalesQueryBuilder.php";

class NewOrder implements Controller {
    private $queryBuilder;

    public function __construct(
        $id,
        private $pdo,
        private $execution
    ){}

    public function buildModel(){
        $dailySalesQueryBuilder = new DailySalesQueryBuilder($this->pdo, $this->execution);
        $model = new NewOrderModel($this->pdo, $dailySalesQueryBuilder);
        $this->queryBuilder = new NewOrderQueryBuilder($model, $this->pdo, $this->execution, $dailySalesQueryBuilder);
        
    }

    public function query(){
        switch($_SERVER["REQUEST_METHOD"]){
            case "POST":
                $this->queryBuilder->post();
                break;

            default:
                http_response_code(405);
                echo json_encode(["status" => "error", "message" => strtoupper("Method not allowed.")]);
                return;
        }
    }

}
