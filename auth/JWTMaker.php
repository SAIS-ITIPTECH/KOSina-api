<?php

require_once __DIR__ . "/../vendor/autoload.php";

use \Firebase\JWT\JWT;

class JWTMaker
{
    public function __construct(
        private string $clientId,
        private string $clientName,
        private string $restaurantName,
        private string $role,
        private string $logoUrl,
        private int    $expiry
    ) {}

    public function createToken(): void
    {
        $token = JWT::encode(
            [
                "iat"  => time(),
                "nbf"  => time(),
                "exp"  => time() + $this->expiry,
                "data" => ["id" => $this->clientId],
            ],
            getenv("JWT_KEY"),
            "HS256"
        );

        echo json_encode([
            "token"      => $token,
            "name"       => $this->clientName,
            "resto"      => $this->restaurantName,
            "role"       => $this->role,
            "logo"       => $this->logoUrl,
            "expiration" => $this->expiry,
        ]);
    }
}
