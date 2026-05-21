<?php

require_once __DIR__ . "/LoginModel.php";
require_once __DIR__ . "/LoginQuery.php";
require_once __DIR__ . "/JWTMaker.php";

class LoginController
{
    private LoginModel $model;
    private LoginQuery $query;

    public function __construct()
    {
        $this->model = new LoginModel();
        $this->query = new LoginQuery();
    }

    public function start(): void
    {
        if (!$this->model->validateInputs()) {
            return;
        }
        if (!$this->model->getAccount()) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "WRONG USERNAME OR PASSWORD!"]);
            return;
        }
        if (!$this->model->verifyPassword()) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "WRONG USERNAME OR PASSWORD!"]);
            return;
        }

        $info   = $this->query->getUserInfo($this->model->getUsername());
        $expiry = ($info[0]["role"] === "kiosk") ? (60 * 60) * 24 : (60 * 60) * 2;

        $jwt = new JWTMaker(
            $info[0]["account_id"],
            $info[0]["first_name"] . " " . $info[0]["last_name"],
            $info[0]["name"],
            $info[0]["role"],
            $info[0]["logo_url"],
            $expiry
        );
        $jwt->createToken();
    }
}
