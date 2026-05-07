<?php

require_once "./includes/Database/CredentialsGraber.php";
require_once "./includes/Database/Database.php";

class ConfirmPaid{
    public function checkPaid(){
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $result = json_decode(file_get_contents("php://input"), true) ?? [];
        }

        $getDb = new CredentialsGraber($result['data']['attributes']['data']['attributes']['line_items'][0]['name']);
        $getDb->connectCredentials("resto_name");
        
        $database = new Database("localhost", $getDb->getDbName(),  $getDb->getDbUsername(),  $getDb->getDbPassword());
        $pdo = $database->connectDatabase();
    
        $paymentStatus = $result['data']['attributes']['data']['attributes']['payments'][0]['attributes']['status'];
        if ($paymentStatus === "paid"){
            $stmt = $pdo->prepare('UPDATE ordertest SET paid = true WHERE orderId = :setid');
            $stmt->bindValue(":setid", $result['data']['attributes']['data']['attributes']['line_items'][0]['description']);
            $stmt->execute();
        }

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper("ORDER HAS BEEN PAID")]);
    }
}