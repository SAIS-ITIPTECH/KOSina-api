<?php
    require_once "includes/Controller/Controller.php";
    require_once "DetailsModel.php";
    require_once "DetailsQueryBuilder.php";

    class Details implements Controller {
        private $queryBuilder;

        public function __construct(
            private $id,
            private $pdo,
            private $execution
        ){}

        public function buildModel(){
            $model = new DetailsModel();
            $this->queryBuilder = new DetailsQueryBuilder($model, $this->pdo, $this->execution);
        }
 
        public function query(){
            switch($_SERVER["REQUEST_METHOD"]){
                case "GET":
                    $this->queryBuilder->get();
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