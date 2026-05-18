<?php

require_once __DIR__ . "/../../../services/ImgBB/ImgBB.php";
require_once __DIR__ . "/../../Validation/Validation.php";
require_once __DIR__ . "/../../Database/Database.php";
     
class ImageModel{
    private $img;
    private $productId;
    private $imageId;
    private $displayUrl;
    private $validator;
    private $userInput;

    public function __construct(private $pdo) {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
        }
        $this->validator = new Validation();
    }

    public function validateAll(){
        $img = $this->imgValidator();
        if (!$img) { return false; }
        $productId = $this->productIdValidator();
        if (!$productId) { return false; }
 
        $this->img =  $img;
        $this->productId =  $productId;
        $this->imageId =  rand(0,99999999);

        if ($_SERVER['REQUEST_METHOD'] === "PATCH") {
            $imageId = $this->imageIdValidator();
            if (!$imageId) { return false; }
            $this->imageId = $imageId;
        }

        return true;
    }

    public function productIdValidator(){
        $value = $this->validator->checkEmpty("PRODUCT ID", $this->userInput["productId"] ?? null);
        if(isset($value)) { $value = $this->validator->checkNumber("PRICE", $value); }
        return $value;
    }

    public function imageIdValidator(){
        $value = $this->validator->checkEmpty("Image ID", $this->userInput["imageId"] ?? null);
        if(isset($value)) { $value = $this->validator->checkNumber("PRICE", $value); }
        return $value;
    }

    public function validateId($id){
        $value = $this->validator->checkEmpty("Image ID", $id ?? null);
        if(isset($value)) { $value = $this->validator->checkNumber("PRICE", $value); }
        return $value;
    }
    
    public function imgValidator(){
        if (!isset($this->userInput["image"])){
            http_response_code(415);
            echo json_encode([
                "status" => "error",
                "message" => strtoupper("THERE IS NO IMAGE!")
            ]);
            return null;
        }
        
        $value = $this->userInput["image"];
        $type = explode("/", explode(";", explode(":", $value)[1])[0])[1];
        $allowed = ["jpg", "jpeg", "png", "bmp", "gif", "tiff", "webp"];
        if (!in_array($type, $allowed)) {
            http_response_code(415);
            echo json_encode([
                "status" => "error",
                "message" => strtoupper("UNSUPPORTED FILE FORMAT!")
            ]);
            return null;
        }

        return $value;
    }

    public function uploadImage(){
        $imgBB = new ImgBB();
        $results = $imgBB->upload(explode(",", $this->img)[1]);
        $this->displayUrl = $results["data"]["display_url"];
    }

    public function checkDuplicate($id){
        $query = "SELECT * FROM product_images WHERE product_id = :setid";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $id);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (sizeof($result) != 0) {
            http_response_code(409);
            echo json_encode(["status" => "error", "message" => strtoupper("THIS PRODUCT HAS ALREADY AN IMAGE")]);
            return false;
        }
        return true;
    }

    public function getProductId() { return $this->productId; }
    public function getImageId() { return $this->imageId; }
    public function getDisplayUrl() { return $this->displayUrl; }
}

