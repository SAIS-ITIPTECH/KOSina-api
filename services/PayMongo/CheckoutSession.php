<?php

require_once __DIR__ . "/../../vendor/autoload.php";

class CheckoutSession{
    private $totalPrice;
    private $orderId;
    public function __construct($totalPrice, private $restoName, $orderId, private $apiKey = null){
        $this->totalPrice = $totalPrice * 100;
        $this->orderId =  (string) $orderId;
        $this->client = new \GuzzleHttp\Client();
    }

    public function createSession(){
        $response = $this->client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
        'body' => '{"data":{"attributes":{"send_email_receipt":false,"show_description":true,"show_line_items":true,"success_url": "https://sais-itiptech.github.io/KOSina-Kiosk-Ordering-System/Kiosk-User-Interface/pages/finishCheckout.html","line_items":[{"currency":"PHP","description":"'."$this->orderId".'","amount":'."$this->totalPrice".',"name":"'."$this->restoName".'","quantity":1}],"payment_method_types":["qrph"]}}}',
        'headers' => [
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
            'authorization' => "Basic ". getenv('PAYMONGO_KEY')
        ],
        ]);

        return json_decode($response->getBody(), true);
    }

    public function checkPaid($id){
        $response = $this->client->request('GET', "https://api.paymongo.com/v1/checkout_sessions/{$id}", [
        'headers' => [
            'accept' => 'application/json',
            'authorization' => "Basic ". getenv('PAYMONGO_KEY')
        ],
        ]);
        echo $response->getBody();
    }
}

