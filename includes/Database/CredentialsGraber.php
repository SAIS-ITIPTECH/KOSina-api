<?php

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Execution.php";


class CredentialsGraber{
    private $pdo;
    private $execution;
    private $dbName;
    private $dbUsername;
    private $dbPassword;


    public function __construct(private $id){
        $database = new Database(getenv("DATABASE_HOSTNAME"), getenv("DATABASE_NAME"), getenv("DATABASE_USERNAME"), getenv("DATABASE_PASSWORD"));
        $this->pdo = $database->connectDatabase();
        $this->execution = new Execution();
        
    }

    public function connectCredentials($target){
        error_log("--- NEW 3 WEBHOOK ---");
        error_log(json_encode($target));
        $query = "SELECT db_name, db_username, db_password FROM accounts WHERE $target = :setid";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", "$this->id");
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        error_log("--- NEW 4 WEBHOOK ---");
        error_log(json_encode($result));
        $this->dbName = $result[0]["db_name"] ?? null;
        $this->dbUsername = $result[0]["db_username"] ?? null;
        $this->dbPassword = $result[0]["db_password"] ?? null;
    }

    public function getDbName(){
        return $this->dbName;
    }

    public function getDbUsername(){
        return $this->dbUsername;
    }

    public function getDbPassword(){
        return $this->dbPassword;
    }
    
}