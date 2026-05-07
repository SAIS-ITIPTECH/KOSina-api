<?php
require_once __DIR__ . "LoginModel.php";
require_once __DIR__ . "LoginQuery.php";
require_once __DIR__ . "JWTMaker.php";

class LoginController{
    private $model;
    private $query;

    public function __construct(){
        $this->model = new LoginModel();
        $this->query = new LoginQuery();
    }

    public function start(){
        if (!$this->model->validateInputs()) { return null; }
        if (!$this->model->getAccount()) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => strtoupper("WRONG USERNAME OR PASSWORD!")]);
            return null; 
        }
        if (!$this->model->identifyPassword()) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => strtoupper("WRONG USERNAME OR PASSWORD!")]);
            return null;
        }

        $info = $this->query->getUserInfo($this->model->getUsername());
        $jwt = new JWTMaker($info[0]["account_id"], $info[0]["first_name"] . " " . $info[0]["last_name"] , $info[0]["name"], 60 * 60);
        $jwt->createToken();
        return;
    }
    
}

