<?php

include_once __DIR__ . "/LoginQuery.php";
require_once __DIR__ . "/../includes/Validation/Validation.php";

class LoginModel
{
    private string $username;
    private string $password;
    private string $hashedPassword = "";

    public function __construct()
    {
        header("Content-Type: application/json; charset=utf-8");
        $userInput      = json_decode(file_get_contents("php://input"), true);
        $this->username = $userInput["username"] ?? "";
        $this->password = $userInput["password"] ?? "";
    }

    public function getAccount(): bool
    {
        $query  = new LoginQuery();
        $result = $query->getUserInfo($this->username);
        if (!$result) {
            return false;
        }
        $this->hashedPassword = $result[0]["password"];
        return true;
    }

    public function verifyPassword(): bool
    {
        return password_verify($this->password, $this->hashedPassword);
    }

    public function validateInputs(): bool
    {
        $validation = new Validation();
        if ($validation->checkEmpty("USERNAME", $this->username) === null)                                         { return false; }
        if ($validation->checkSpecial("USERNAME", $this->username, '/[^a-zA-Z0-9 _\-.]/')  === null)              { return false; }
        if ($validation->checkEmpty("PASSWORD", $this->password) === null)                                        { return false; }
        return true;
    }

    public function getUsername(): string
    {
        return $this->username;
    }
}
