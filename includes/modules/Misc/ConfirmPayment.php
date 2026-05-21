<?php

require_once __DIR__ . "/../../Database/CredentialsGrabber.php";
require_once __DIR__ . "/../../Database/Database.php";

class ConfirmPayment
{
    private PDO    $pdo;
    private string $orderId;

    public function checkCashless(): void
    {
        $payload = json_decode(file_get_contents("php://input"), true) ?? [];

        $restaurantName = $payload["data"]["attributes"]["data"]["attributes"]["line_items"][0]["name"];
        $grabber        = new CredentialsGrabber($restaurantName);
        $grabber->connectByName();

        $database  = new Database(
            getenv("DATABASE_HOSTNAME"),
            $grabber->getDbName(),
            $grabber->getDbUsername(),
            $grabber->getDbPassword()
        );
        $this->pdo = $database->connectDatabase();

        $paymentStatus = $payload["data"]["attributes"]["data"]["attributes"]["payments"][0]["attributes"]["status"];

        if ($paymentStatus === "paid") {
            $this->orderId = $payload["data"]["attributes"]["data"]["attributes"]["line_items"][0]["description"];
            $this->confirmPayment();
        }
    }

    public function checkCash(array $dbCredentials, string $orderId): void
    {
        $this->orderId = $orderId;
        $database      = new Database(
            getenv("DATABASE_HOSTNAME"),
            $dbCredentials["dbName"],
            $dbCredentials["dbUsername"],
            $dbCredentials["dbPassword"]
        );
        $this->pdo = $database->connectDatabase();
        $this->confirmPayment();
    }

    private function confirmPayment(): void
    {
        $stmt = $this->pdo->prepare("UPDATE order_history SET paid = true WHERE order_id = :setId");
        $stmt->bindValue(":setId", $this->orderId);
        $stmt->execute();
        $this->updateDailySales();
    }

    private function updateDailySales(): void
    {
        $stmt = $this->pdo->prepare("SELECT daily_sale_id FROM order_history WHERE order_id = :setId");
        $stmt->bindValue(":setId", $this->orderId);
        $stmt->execute();
        $dailySaleId = $stmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            UPDATE daily_sales
            SET
                total_income = COALESCE((
                    SELECT SUM(total_price)
                    FROM order_history
                    WHERE daily_sale_id = :setId1 AND paid = 1
                ), 0),
                total_sales = (
                    SELECT COUNT(*)
                    FROM order_history
                    WHERE daily_sale_id = :setId2 AND paid = 1
                )
            WHERE daily_sale_id = :setId3
        ");
        $stmt->bindValue(":setId1", $dailySaleId);
        $stmt->bindValue(":setId2", $dailySaleId);
        $stmt->bindValue(":setId3", $dailySaleId);
        $stmt->execute();

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "ORDER HAS BEEN PAID"]);
    }
}
