<?php
require_once __DIR__ . "/../../Validation/Validation.php";

class ProductModel {
    private $name;
    private $price;
    private $categoryId;
    private $available;
    private $userInput;
    private $validator;
    private $id;

    public function __construct(){
        $this->validator = new Validation();
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
        }
    }

    public function validateFields(){
        $name = $this->nameValidator();
        if (!$name) { return false; }
        $price = $this->priceValidator();
        if (!$price) { return false; }
        $categoryId = $this->categoryIdValidator();
        if (!$categoryId) { return false; }
        $available = $this->availableValidator();
        if ($available === null) { return false; }
        $this->name = $name;
        $this->price = $price;
        $this->categoryId = $categoryId;
        $this->available = ($available === true);

        return true;
    }

    public function validateId($dirtyId){
        $id = $this->idValidator($dirtyId);
        if(!$id) { return false; }
        $this->id = $id;
        return true;
    }

    private function nameValidator(){
        $value = $this->validator->checkEmpty("NAME", $this->userInput["name"] ?? null);
        if(isset($value)) { $value = $this->validator->checkSpecial("NAME", $value, '/[^a-zA-Z0-9 _\-.]/'); }
        return $value;
    }

    private function priceValidator(){
        $value = $this->validator->checkEmpty("PRICE", $this->userInput["price"] ?? null);
        if(isset($value)) { $value = $this->validator->checkNumber("PRICE", $value); }
        return $value;
    }

    private function categoryIdValidator(){
        $value = $this->validator->checkEmpty("CATEGORY ID", $this->userInput["categoryId"] ?? null);
        if(isset($value)) { $value = $this->validator->checkSpecial("CATEGORY ID", $value, '/[^a-zA-Z0-9 _\-.]/'); }
        return $value;
    }

    private function availableValidator(){
        return $this->validator->checkEmpty("AVAILABLE", $this->userInput["available"] ?? null);
    }

    private function idValidator($dirtyId){
        if(empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("THE PRODUCT ID IS INVALID.")]);
            return false;
        }
        return $dirtyId;
    }

    public function getName(){ return $this->name; }
    public function getPrice(){ return $this->price; }
    public function getCategoryId(){ return $this->categoryId; }
    public function getAvailable(){ return $this->available; }
    public function getId(){ return $this->id; }
    public function getUserInput(){ return $this->userInput; }
}