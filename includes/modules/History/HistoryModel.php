<?php

require_once __DIR__ . "/../../Validation/Validation.php";

class HistoryModel {
    private $totalPrice;
    private $paid;
    private $paymentMethod;
    private $userInput;
    private $validator;

    public function __construct(){
        $this->validator = new Validation();
        $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
    }

    public function validateFields(){
        $totalPrice   = $this->totalPriceValidator();
        if ( !$totalPrice ) { return false; }
        $paid = $this->paidValidator();
        if ( !$paid ) { return false; }
        $paymentMethod  = $this->paymentMethodValidator();
        if ( !$paymentMethod ) { return false; }

        $this->totalPrice   = $totalPrice;
        $this->paid = $paid;
        $this->paymentMethod  = $paymentMethod;

        return true;
    }

    private function totalPriceValidator(){
        $value = $this->validator->checkEmpty("TOTAL PRICE", $this->userInput["totalPrice"] ?? null);
        if(isset($value)) $value = $this->validator->checkNumber("ORDER ID", $value);
        return $value;
    }

    private function paidValidator(){
        $value = $this->validator->checkEmpty("PAID", $this->userInput["paid"] ?? null);
        return $value;
    }

    private function paymentMethodValidator(){
        $value = $this->validator->checkEmpty("PAYMENT METHOD", $this->userInput["paymentMethod"] ?? null);
        return $value;
    }

    public function idValidator($dirtyId){
        if(empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("The item ID is invalid.")]);
            return false;
        }
        if(!$this->validator->checkSpecial("CTATEGORY ID", $dirtyId, '/[^a-zA-Z0-9\-]/')) { return false; }
        return true;
    }

    public function datePageValidator($dirtyDatePage){
        if($this->validator->checkEmpty("DATE PAGE", $dirtyDatePage) === null) { return false; }
        if($this->validator->checkSpecial("DATE PAGE", $dirtyDatePage, '/[^a-zA-Z0-9\-]/') === null) { return false; }
        return false;
    }

    public function pageValidator($dirtyPage){
        if($this->validator->checkEmpty("PAGE", $dirtyPage) === null) { return false; }
        if($this->validator->checkNumber("PAGE", $dirtyPage === null)) { return false; }
        return true;
    }

    public function getTotalPrice(){ return $this->totalPrice; }
    public function getPaid(){ return $this->paid; }
    public function getPaymentMethod(){ return $this->paymentMethod; }
}