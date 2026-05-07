<?php

include_once "LoginQuery.php";
require_once "./includes/Validation/Validation.php";


class LoginModel{
    private $username;
    private $password;
    private $dbUsername;
    private $dbPassword;

    public function __construct(){
        header("Content-Type: application/json; charset=utf-8");
        $userInfo = json_decode(file_get_contents("php://input"), true);
        $this->username = $userInfo["username"] ?? "";
        $this->password = $userInfo["password"] ?? "";
    }

    public function getAccount(){
        $query = new LoginQuery();
        $result = $query->getUserInfo($this->username);
        if (!$result) { return false; }
        $this->dbUsername = $result[0]["username"];
        $this->dbPassword = $result[0]["password"];
        return true;
    }


    public function identifyPassword(){
        if (!password_verify($this->password, $this->dbPassword)){ return false; }
        return true;
    }

    public function validateInputs(){
        $validation = new Validation();
        if ($validation->checkEmpty("USERNAME",$this->username) == null) { return null; }
        if ($validation->checkSpecial("USERNAME",$this->username) == null) { return null; }
        if ($validation->checkEmpty("PASSWORD",$this->password) == null) { return null; }
        return true;
    }

    public function getUsername(){
        return $this->username;
    }
}