<?php

require_once __DIR__ . "/Database.php";
require_once __DIR__ . "/Execution.php";

class CredentialsGrabber
{
    private PDO $pdo;
    private Execution $execution;
    private string|null $dbName     = null;
    private string|null $dbUsername = null;
    private string|null $dbPassword = null;
    private string|null $role       = null;

    public function __construct(private string $id)
    {
        $database        = new Database(
            getenv("DATABASE_HOSTNAME"),
            getenv("DATABASE_NAME"),
            getenv("DATABASE_USERNAME"),
            getenv("DATABASE_PASSWORD")
        );
        $this->pdo       = $database->connectDatabase();
        $this->execution = new Execution();
    }

    public function connectById(): void
    {
        $query = "
            SELECT db_accounts.db_name, db_accounts.db_username, db_accounts.db_password, accounts.role
            FROM db_accounts
            INNER JOIN clients  ON clients.client_id  = db_accounts.client_id
            INNER JOIN accounts ON accounts.client_id = clients.client_id
            WHERE accounts.account_id = :setId
        ";
        $this->bind($query);
    }

    public function connectByName(): void
    {
        $query = "
            SELECT db_accounts.db_name, db_accounts.db_username, db_accounts.db_password, accounts.role
            FROM db_accounts
            INNER JOIN clients  ON clients.client_id  = db_accounts.client_id
            INNER JOIN accounts ON accounts.client_id = clients.client_id
            WHERE clients.name = :setId
        ";
        $this->bind($query);
    }

    private function bind(string $query): void
    {
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setId", $this->id);
        $this->execution->execute($stmt);
        $result           = $this->execution->getResults();
        $this->dbName     = $result[0]["db_name"]     ?? null;
        $this->dbUsername = $result[0]["db_username"]  ?? null;
        $this->dbPassword = $result[0]["db_password"]  ?? null;
        $this->role       = $result[0]["role"]         ?? null;
    }

    public function getDbName(): string|null     { return $this->dbName; }
    public function getDbUsername(): string|null { return $this->dbUsername; }
    public function getDbPassword(): string|null { return $this->dbPassword; }
    public function getRole(): string|null       { return $this->role; }
}
