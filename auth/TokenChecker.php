<?php

require __DIR__ . "/../vendor/autoload.php";

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;

class TokenChecker
{
    private string|null $token;

    public function __construct()
    {
        $authHeader  = $_SERVER["HTTP_AUTHORIZATION"] ?? $_SERVER["REDIRECT_HTTP_AUTHORIZATION"] ?? "";
        $bearerToken = str_replace("Bearer ", "", $authHeader);
        $this->token = (!empty($bearerToken) && $bearerToken !== "null") ? $bearerToken : null;
    }

    public function checkToken(): void
    {
        if ($this->token !== null) {
            echo json_encode($this->decodeToken());
        } else {
            echo json_encode(["status" => "error", "message" => "SESSION EXPIRED"]);
        }
    }

    public function decodeToken(): mixed
    {
        try {
            $decoded = JWT::decode($this->token, new Key(getenv("JWT_KEY"), "HS256"));
            return $decoded->data->id;

        } catch (ExpiredException $e) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "SESSION EXPIRED"]);
            exit;

        } catch (SignatureInvalidException $e) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "INVALID TOKEN"]);
            exit;

        } catch (BeforeValidException $e) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "TOKEN NOT YET VALID"]);
            exit;

        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "UNAUTHORIZED"]);
            exit;
        }
    }
}
