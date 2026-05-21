<?php

class Execution
{
    private array $results = [];

    public function execute(PDOStatement $stmt): void
    {
        $stmt->execute();
        $this->results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getResults(): array
    {
        return $this->results;
    }

    public function sendMessage(mixed $message): void
    {
        echo json_encode($message);
    }
}
