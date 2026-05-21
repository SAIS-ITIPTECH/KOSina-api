<?php

require_once __DIR__ . "/../includes/Database/Execution.php";

class LoginQuery
{
    private PDO $pdo;
    private Execution $execution;

    public function __construct()
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

    public function getUserInfo(string $username): array|false
    {
        $query = "
            SELECT accounts.*, accounts.role, clients.name, clients.client_id, clients.logo_url
            FROM accounts
            JOIN clients ON accounts.client_id = clients.client_id
            WHERE accounts.username = :setUsername
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setUsername", $username, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        return $this->execution->getResults();
    }
}
