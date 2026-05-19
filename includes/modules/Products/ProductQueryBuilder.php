<?php
require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../../Database/Database.php";

class ProductQueryBuilder {
    private $msg;

    public function __construct(private $model, private $pdo, private $execution){}

    public function get($id){
        $query = !isset($id) ? "SELECT product_list.product_id, product_list.name, product_list.category_id, product_list.price, product_list.available, product_images.display_url, product_images.image_id 
                                FROM product_list 
                                LEFT JOIN product_images ON product_list.product_id = product_images.product_id 
                                WHERE product_list.deleted = false" : 
                                "SELECT product_list.product_id, product_list.name, product_list.category_id, product_list.price, product_list.available, product_images.display_url, product_images.image_id 
                                FROM product_list 
                                LEFT JOIN product_images ON product_list.product_id = product_images.product_id 
                                WHERE product_list.deleted = false AND product_list.category_id = :setid";
        $stmt = $this->pdo->prepare($query);
        if (isset($id)) { $stmt->bindValue(":setid", $id, PDO::PARAM_STR); }
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        http_response_code(200);
        echo json_encode($result);
    }

    public function post(){
        $query = "INSERT INTO product_list (name, price, category_id, available)
                    VALUES (:setName, :setPrice, :setCategoryId, :setAvailable)";

        $stmt = $this->pdo->prepare($query);
        $this->superBind($stmt, "has been added.");
    }

    public function update($id){
        if(!$this->model->validateId($id)) return;
        echo "sdadasdass";
        $query = "UPDATE product_list SET name = :setName, price = :setPrice, category_id = :setCategoryId, available = :setAvailable WHERE product_id = :setid";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $this->model->getId(), PDO::PARAM_INT);
        $this->superBind($stmt, "has been updated.");
    }

    public function delete($id){
        if (!$this->model->validateId($id)) { return; }

        $query = "UPDATE product_list SET deleted = true WHERE product_id = :setid";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $this->model->getId(), PDO::PARAM_INT);
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper("DELETED SUCCESSFULLY")]);
    }

    private function superBind($stmt, $secondMessage){
        if(!$this->model->validateFields()) return;
        error_log("{$this->model->getName()}, {$this->model->getPrice()}, {$this->model->getCategoryId()}, {$this->model->getAvailable()}");
        
        $stmt->bindValue(":setName", $this->model->getName(), PDO::PARAM_STR);
        $stmt->bindValue(":setPrice", $this->model->getPrice());
        $stmt->bindValue(":setCategoryId", $this->model->getCategoryId(), PDO::PARAM_STR);
        $stmt->bindValue(":setAvailable", $this->model->getAvailable(), PDO::PARAM_BOOL);

        $this->msg = "{$this->model->getName()} {$secondMessage}";
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }
}