<?php

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Execution.php";


class CredentialsGraber{
    private $pdo;
    private $execution;
    private $dbName;
    private $dbUsername;
    private $dbPassword;
    private $role;


    public function __construct(private $id){
        $database = new Database(getenv("DATABASE_HOSTNAME"), getenv("DATABASE_NAME"), getenv("DATABASE_USERNAME"), getenv("DATABASE_PASSWORD"));
        $this->pdo = $database->connectDatabase();
        $this->execution = new Execution();
    }

    public function connectCredentialsId(){
        $query = "
            SELECT db_accounts.db_name,  db_accounts.db_username,  db_accounts.db_password, accounts.role
            FROM db_accounts
            INNER JOIN clients ON clients.client_id = db_accounts.client_id
            INNER JOIN accounts ON accounts.client_id = clients.client_id
            WHERE accounts.account_id = :setid;
        ";
        $this->bind($query);
    }

    public function connectCredentialsName(){
        $query = "
            SELECT db_accounts.db_name,  db_accounts.db_username,  db_accounts.db_password, accounts.role
            FROM db_accounts
            INNER JOIN clients ON clients.client_id = db_accounts.client_id
            INNER JOIN accounts ON accounts.client_id = clients.client_id
            WHERE clients.name = :setid
        ";
        $this->bind($query);
    }

    private function bind($query){
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", "$this->id");
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        $this->dbName = $result[0]["db_name"] ?? null;
        $this->dbUsername = $result[0]["db_username"] ?? null;
        $this->dbPassword = $result[0]["db_password"] ?? null;
        $this->role = $result[0]["role"] ?? null;
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

    public function getRole(){
        return $this->role;
    }
    
}