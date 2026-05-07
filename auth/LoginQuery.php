<?php
require_once __DIR__ . "/../includes/Database/Execution.php";

class LoginQuery{
    private $pdo;
    private $execution;

    public function __construct(){
        $database = new Database(getenv("DATABASE_HOSTNAME"), getenv("DATABASE_NAME"), getenv("DATABASE_USERNAME"));
        $this->pdo = $database->connectDatabase();
        $this->execution = new Execution;
    }
    
    public function getUserInfo($username){
        $query = "SELECT * FROM accounts JOIN clients ON accounts.client_id = clients.client_id WHERE accounts.username = :setusername;";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setusername", $username, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        return $this->execution->getResults();
    }
}