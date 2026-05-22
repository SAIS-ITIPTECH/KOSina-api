<?php

require_once __DIR__ . "/../../vendor/autoload.php";

class CheckoutSession
{
    private float  $totalPrice;
    private string $orderId;
    private \GuzzleHttp\Client $client;

    public function __construct(
        float|null  $totalPrice,
        private string|null $restaurantName,
        string|null $orderId
    ) {
        $this->totalPrice = ($totalPrice ?? 0) * 100;
        $this->orderId    = (string) ($orderId ?? "");
        $this->client     = new \GuzzleHttp\Client();
    }

    public function createSession(): array
    {
        $payload = json_encode([
            "data" => [
                "attributes" => [
                    "send_email_receipt" => false,
                    "show_description"   => true,
                    "show_line_items"    => true,
                    "success_url"        => "https://sais-itiptech.github.io/KOSina-Kiosk-Ordering-System/Kiosk-User-Interface/pages/finishCheckout.html",
                    "line_items"         => [[
                        "currency"    => "PHP",
                        "description" => $this->orderId,
                        "amount"      => $this->totalPrice,
                        "name"        => $this->restaurantName,
                        "quantity"    => 1,
                    ]],
                    "payment_method_types" => ["qrph"],
                ],
            ],
        ]);

        $response = $this->client->request("POST", "https://api.paymongo.com/v1/checkout_sessions", [
            "body"    => $payload,
            "headers" => [
                "Content-Type" => "application/json",
                "accept"       => "application/json",
                "authorization" => "Basic " . getenv("PAYMONGO_KEY"),
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function checkPaid(string|null $sessionId = null): void
    {
        if (!isset($sessionId)) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "THIS METHOD NEEDS AN ID!"]);
            return;
        }
        $response = $this->client->request("GET", "https://api.paymongo.com/v1/checkout_sessions/{$sessionId}", [
            "headers" => [
                "accept"        => "application/json",
                "authorization" => "Basic " . getenv("PAYMONGO_KEY"),
            ],
        ]);

        echo $response->getBody();
    }
}
