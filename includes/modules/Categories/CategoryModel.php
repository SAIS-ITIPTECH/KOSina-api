<?php
require_once __DIR__ . "/../../Validation/Validation.php";

class CategoryModel{
    private $categoryId;
    private $name;
    private $displayIndex;
    private $userInput;
    private $validator;
    private $id;

    public function __construct(){
        $this->validator = new Validation;
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
        }
    }

    public function validateFields(){
        $categoryId = $this->catValidator();
        if (!$categoryId) { return false; }
        $name = $this->nameValidator();
        if (!$name) { return false; }
        $displayIndex = $this->indexValidator();
        if (!$displayIndex) { return false; }

        $this->categoryId =  $categoryId;
        $this->name =  $name;
        $this->displayIndex =  $displayIndex;

        return true;
    }

    public function validateId($dirtyId){
        $id = $this->idValidator($dirtyId);
        if (!$id) { return false; }
        $this->id = $id;
        return true;
    }

    private function catValidator(){
        $value = $this->validator->checkEmpty("CATEGORY ID", $this->userInput["categoryId"] ?? null);
        if(isset($value)) { $value = $this->validator->checkSpecial("CATEGORY ID", $value, '/[^a-zA-Z0-9 _\-.]/'); }
        return $value;
    }

    private function nameValidator(){
        return $this->validator->checkEmpty("NAME", $this->userInput["name"] ?? null );
    }

    private function indexValidator(){
        (int) $value = $this->validator->checkEmpty("INDEX", $this->userInput["displayIndex"] ?? null);
        if(isset($value)) { $value = $this->validator->checkNumber("INDEX", $value); }
        return $value;
    }

    private function idValidator($dirtyId){
        if(empty($dirtyId) || preg_match('/[^a-zA-Z0-9]/', $dirtyId)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("THE CATEGORY ID IS INVALID.")]);
            return false;
        } else {return $dirtyId;}
    }

    public function getCategoryId() { return $this->categoryId; }
    public function getName() { return $this->name; }
    public function getDisplayIndex() { return $this->displayIndex; }
    public function getId() { return $this->id; }
}