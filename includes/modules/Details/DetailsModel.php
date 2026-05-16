<?php
require_once __DIR__ . "/../../Validation/Validation.php";

class DetailsModel {
    private $orderId;
    private $productId;
    private $quantity;
    private $userInput;
    private $validator;

    public function __construct(){
        $this->validator = new Validation();
        $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
    }

    public function validateFields(){
        $orderId   = $this->orderIdValidator();
        if (!$orderId) { return false; }
        $productId = $this->productIdValidator();
        if (!$productId) { return false; }
        $quantity  = $this->quantityValidator();
        if (!$quantity) { return false; }

        $this->orderId   = $orderId;
        $this->productId = $productId;
        $this->quantity  = $quantity;

        return true;
    }

    private function orderIdValidator(){
        $value = $this->validator->checkEmpty("ORDER ID", $this->userInput["orderId"] ?? null);
        if(isset($value)) $value = $this->validator->checkSpecial("ORDER ID", $value, '/[^a-zA-Z0-9\-]/');
        return $value;
    }

    private function productIdValidator(){
        $value = $this->validator->checkEmpty("PRODUCT ID", $this->userInput["productId"] ?? null);
        if(isset($value)) $value = $this->validator->checkSpecial("PRODUCT ID", $value, '/[^a-zA-Z0-9\-]/');
        return $value;
    }
 
    private function quantityValidator(){
        $value = $this->validator->checkEmpty("QUANTITY", $this->userInput["quantity"] ?? null);
        if(isset($value)) $value = $this->validator->checkNumber("QUANTITY", $value);
        return $value;
    }

    public function idValidator($dirtyId){
        if(empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("The date is invalid.")]);
            return false;
        }
        if(!$this->validator->checkSpecial("CTATEGORY ID", $dirtyId, '/[^a-zA-Z0-9\-]/')) { return false; }
        return true;
    }

    public function datePageValidator($dirtyDatePage){
        if($this->validator->checkEmpty("DATE PAGE", $dirtyDatePage) === null) { return false; }
        if($this->validator->checkSpecial("DATE PAGE", $dirtyDatePage, '/[^a-zA-Z0-9\-]/') === null) { return false; }
        return true;
    }

    public function pageValidator($dirtyPage){
        if($this->validator->checkEmpty("PAGE", $dirtyPage) === null) { return false; }
        if($this->validator->checkNumber("PAGE", $dirtyPage) === null) { return false; }
        return true;
    }

    public function getOrderId(){ return $this->orderId; }
    public function getProductId(){ return $this->productId; }
    public function getQuantity(){ return $this->quantity; }
}