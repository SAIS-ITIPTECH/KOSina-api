<?php
require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../../Database/Database.php";

class ImageQueryBuilder{
    private $msg;

    public function __construct(
        private $model,
        private $pdo,
        private $execution
    ){}

    public function get(){
        $query = "SELECT * FROM product_images ORDER BY product_id ASC";
        $stmt = $this->pdo->prepare($query);
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        http_response_code(200);
        echo json_encode($result);
    }

    public function post(){
        if (!$this->model->validateAll()){return;}
        if(!$this->model->checkDuplicate($this->model->getProductId())) { return null; }
        $query = "INSERT INTO product_images (product_id, image_id, display_url)
                VALUES (:setProductId, :setImageId, :setDisplayUrl)";

        $stmt = $this->pdo->prepare($query);
        $this->superBind("IMAGE HAS BEEN ADDED", $stmt);
    }

    public function update($id){
        if (!$this->model->validateAll()){return;}

        $query = "UPDATE product_images SET product_id = :setProductId, image_id = :setImageId , display_url = :setDisplayUrl  WHERE product_id = :setid";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $this->model->getProductId(), PDO::PARAM_STR);
        $this->superBind("HAS BEEN UPDATED", $stmt);
    }

    public function delete($id){
        $query = "DELETE FROM product_images WHERE image_id = :setid";
        $id = $this->model->validateId($id);
        if (!$id) { return; }
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $id, PDO::PARAM_INT);
        $this->msg = "IMAGE HAS BEEN DELETED";
        $this->execution->execute($stmt);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }

    private function superBind($secondMessage, $stmt){
        
        $this->model->uploadImage();
        $stmt->bindValue(":setProductId", $this->model->getProductId(), PDO::PARAM_INT);
        $stmt->bindValue(":setImageId", $this->model->getImageId(), PDO::PARAM_STR);
        $stmt->bindValue(":setDisplayUrl", $this->model->getDisplayUrl(), PDO::PARAM_STR);

        $this->msg = "{Image {$secondMessage}";
        $this->execution->execute($stmt);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }
}