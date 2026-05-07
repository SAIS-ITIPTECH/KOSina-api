<?php
require_once __DIR__ . "/../../Validation/Validation.php";

class DetailsModel {
    private $orderId;
    private $productId;
    private $quantity;
    private $userInput;
    private $validator;
    private $id;

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

    public function validateId($dirtyId){
        $id = $this->idValidator($dirtyId);
        if(!$id) return false;
        $this->id = $id;
        return true;
    }

    private function orderIdValidator(){
        $value = $this->validator->checkEmpty("ORDER ID", $this->userInput["orderId"] ?? null);
        if(isset($value)) $value = $this->validator->checkNumber("ORDER ID", $value);
        return $value;
    }

    private function productIdValidator(){
        $value = $this->validator->checkEmpty("PRODUCT ID", $this->userInput["productId"] ?? null);
        if(isset($value)) $value = $this->validator->checkNumber("PRODUCT ID", $value);
        return $value;
    }

    private function quantityValidator(){
        $value = $this->validator->checkEmpty("QUANTITY", $this->userInput["quantity"] ?? null);
        if(isset($value)) $value = $this->validator->checkNumber("QUANTITY", $value);
        return $value;
    }

    private function idValidator($dirtyId){
        if(empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => "The item ID is invalid."]);
            return false;
        }
        return $dirtyId;
    }

    public function getOrderId(){ return $this->orderId; }
    public function getProductId(){ return $this->productId; }
    public function getQuantity(){ return $this->quantity; }
    public function getId(){ return $this->id; }
}