<?php
require_once __DIR__ . "/../vendor/autoload.php";

use \Firebase\JWT\JWT;



class JWTMaker{
    public function __construct(
        private $clientId,
        private $clientName,
        private $restoName,
        private $expiry
        
    ){}

    public function createToken(){
        $key = $_ENV["JWT_KEY"];
        $token = JWT::encode(
            [
                'iat' => time(),
                'nbf' => time(),
                'exp' => time() + $this->expiry,
                'data' => [
                    "id" => $this->clientId
                ]
            ],
            $key,
            'HS256'
        );
        echo json_encode(["token" => $token, "name" => $this->clientName, "resto" => $this->restoName, "expiration" => $this->expiry]);
    }
}