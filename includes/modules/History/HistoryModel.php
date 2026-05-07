<?php

require_once "./includes/Validation/Validation.php";

class HistoryModel {
    private $totalPrice;
    private $paid;
    private $paymentMethod;
    private $userInput;
    private $validator;
    private $id;

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

    public function validateId($dirtyId){
        $id = $this->idValidator($dirtyId);
        if(!$id) return false;
        $this->id = $id;
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

    private function idValidator($dirtyId){
        if(empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("The item ID is invalid.")]);
            return false;
        }
        return $dirtyId;
    }

    public function getTotalPrice(){ return $this->totalPrice; }
    public function getPaid(){ return $this->paid; }
    public function getPaymentMethod(){ return $this->paymentMethod; }
    public function getId(){ return $this->id; }
}