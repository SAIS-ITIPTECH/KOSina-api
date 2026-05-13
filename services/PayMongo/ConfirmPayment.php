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
        echo "why0";
        $this->id = $id;
        var_dump($this->id,$id);
        var_dump($dbCredentials);
        $database = new Database(getenv("DATABASE_HOSTNAME"), $dbCredentials["dbName"], $dbCredentials["dbUsername"], $dbCredentials["dbPassword"]);
        $this->pdo = $database->connectDatabase();
        $this->confirm();
    }

    private function confirm(){
        $stmt = $this->pdo->prepare('UPDATE order_history SET paid = true WHERE order_id= :setid');
        $stmt->bindValue(":setid", $this->id);
        $stmt->execute();
        $this->updateDailySales();
    }

    private function updateDailySales(){
        $stmt = $this->pdo->prepare('SELECT daily_sale_id FROM order_history WHERE order_id = :setid');
        $stmt->bindValue(":setid", $this->id);
        $stmt->execute();
        $dailySaleId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            UPDATE daily_sales 
            SET total_income = CASE
            WHEN ( 
                SELECT SUM(total_price) 
                FROM order_history 
                WHERE order_history.daily_sale_id = :setid1 && paid = 1
            ) IS NOT NULL THEN ( 
                SELECT SUM(total_price) 
                FROM order_history 
                WHERE order_history.daily_sale_id = :setid2 && paid = 1
            )
            
            WHEN ( 
                SELECT SUM(total_price) 
                FROM order_history 
                WHERE order_history.daily_sale_id = :setid3 && paid = 1
            ) IS NULL THEN 0
            END,
            
            total_sales = CASE
            WHEN (
                SELECT COUNT(*) 
                FROM order_history 
                WHERE daily_sale_id = :setid4 && paid = 1
            ) IS NOT NULL THEN (
                SELECT COUNT(*) 
                FROM order_history 
                WHERE daily_sale_id = :setid5 && paid = 1
            )
            
            WHEN (
                SELECT COUNT(*) 
                FROM order_history 
                WHERE daily_sale_id = :setid16 && paid = 1
            ) IS NULL THEN 0
            END
          
            WHERE daily_sale_id = :setid7;
        ");
        $stmt->bindValue(":setid1", $dailySaleId);
        $stmt->bindValue(":setid2", $dailySaleId);
        $stmt->bindValue(":setid3", $dailySaleId);
        $stmt->bindValue(":setid4", $dailySaleId);
        $stmt->bindValue(":setid5", $dailySaleId);
        $stmt->bindValue(":setid6", $dailySaleId);
        $stmt->bindValue(":setid7", $dailySaleId);
        $stmt->execute();
    }

}