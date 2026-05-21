<?php
require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../../Database/CredentialsGrabber.php";
require_once __DIR__ . "/../../../auth/TokenChecker.php";

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
        $query = "
            INSERT INTO product_images (product_id, image_id, display_url)
            VALUES (:setProductId, :setImageId, :setDisplayUrl)
        ";
        $stmt = $this->pdo->prepare($query);
        $this->superBind("IMAGE HAS BEEN ADDED", $stmt);
    }

    public function update($id){
        if (!$this->model->processId($id)){return;}

        if ($this->model->getTarget() === "admin") {
           $this->adminUpdate();
           return;
        }

        if (!$this->model->validateAll()){return;}

        $query = "
            UPDATE product_images
            SET product_id = :setProductId, image_id = :setImageId , display_url = :setDisplayUrl  
            WHERE product_id = :setid
        ";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $this->model->getProductId(), PDO::PARAM_STR);
        $this->superBind("HAS BEEN UPDATED", $stmt);
    }

    public function delete($id){
        echo $id;
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
        $stmt->bindValue(":setProductId", $this->model->getProductId(), PDO::PARAM_STR);
        $stmt->bindValue(":setImageId", $this->model->getImageId(), PDO::PARAM_STR);
        $stmt->bindValue(":setDisplayUrl", $this->model->getDisplayUrl(), PDO::PARAM_STR);

        $this->msg = "{Image {$secondMessage}";
        $this->execution->execute($stmt);

        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }

    private function adminUpdate(){
        $tokenChecker = new TokenChecker();
        $accountId    = $tokenChecker->decodeToken();
        $pdo = (new Database(getenv("DATABASE_HOSTNAME"), getenv("DATABASE_NAME"), getenv("DATABASE_USERNAME"), getenv("DATABASE_PASSWORD")))->connectDatabase();
        $stmt = $pdo->prepare("
            UPDATE clients
            LEFT JOIN accounts ON clients.client_id = accounts.client_id
            SET clients.logo_url = :seturl
            WHERE accounts.account_id = :setid;
        ");

        if (!$this->model->validateAll()){return;}
        $this->model->uploadImage();
        $stmt->bindValue(":seturl", $this->model->getDisplayUrl(), PDO::PARAM_STR);
        $stmt->bindValue(":setid", $accountId, PDO::PARAM_INT);
        $this->execution->execute($stmt);
        echo json_encode(($this->execution->getResults()));
    }
}