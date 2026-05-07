<?php

require_once __DIR__ . "/../../vendor/autoload.php";

class CheckoutSession{
    private $totalPrice;
    private $orderId;
    public function __construct($totalPrice, private $restoName, $orderId, private $apiKey = null){
        $this->totalPrice = $totalPrice * 100;
        $this->orderId =  (string) $orderId;
    }

    public function createSession(){
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', 'https://api.paymongo.com/v1/checkout_sessions', [
        'body' => '{"data":{"attributes":{"send_email_receipt":false,"show_description":true,"show_line_items":true,"line_items":[{"currency":"PHP","description":"'."$this->orderId".'","amount":'."$this->totalPrice".',"name":"'."$this->restoName".'","quantity":1}],"payment_method_types":["qrph"]}}}',
        'headers' => [
            'Content-Type' => 'application/json',
            'accept' => 'application/json',
            'authorization' => "Basic {$_ENV['PAYMONGO_KEY']}"
        ],
        ]);

        return json_decode($response->getBody(), true)["data"]["attributes"]["checkout_url"];
    }
}

