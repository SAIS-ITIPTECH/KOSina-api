<?php

require_once __DIR__ . "/../../includes/Database/CredentialsGraber.php";
require_once __DIR__ . "/../../includes/Database/Database.php";

class ConfirmPayment{
    private $pdo;
    private $id;

    public function checkCashless(){
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $result = json_decode(file_get_contents("php://input"), true) ?? [];
        }

        $getDb = new CredentialsGraber($result['data']['attributes']['data']['attributes']['line_items'][0]['name']);
        $getDb->connectCredentialsName();
        
        $database = new Database(getenv("DATABASE_HOSTNAME"), $getDb->getDbName(), $getDb->getDbUsername(), $getDb->getDbPassword());
        $this->pdo = $database->connectDatabase();
    
        $paymentStatus = $result['data']['attributes']['data']['attributes']['payments'][0]['attributes']['status'];

        if ($paymentStatus === "paid"){
            $this->id = $result['data']['attributes']['data']['attributes']['line_items'][0]['description'];
            $this->confirm();
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper("ORDER HAS BEEN PAID")]);
    }

    public function checkCash($dbCredentials, $id){
        $this->id = $id;
        $database = new Database(getenv("DATABASE_HOSTNAME"), $dbCredentials["dbName"], $dbCredentials["dbUsername"], $dbCredentials["dbPassword"]);
        $this->pdo = $database->connectDatabase();
        $this->confirm();
    }

    private function confirm(){
            $stmt = $this->pdo->prepare('UPDATE order_history SET paid = true WHERE order_id= :setid');
            $stmt->bindValue(":setid", $this->id);
            $stmt->execute();
    }
}