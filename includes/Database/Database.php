<?php
    class Database{
        public function __construct(
            private string $host,
            private string $name,
            private string $user,
            private string $password
        ){}

        public function connectDatabase(): PDO {
            var_dump($this->host, $this->name, $this->user, $this->password);
            $dsn = "mysql:$this->host;dbname=$this->name;charset=utf8";
            $pdo = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
            return $pdo;
        }
    }