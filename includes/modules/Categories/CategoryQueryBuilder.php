<?php
require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../../Database/Database.php";

class CategoryQueryBuilder{
    private $msg;

    public function __construct(private $model, private $pdo, private $execution){}

    public function get(){
        $query = "
            SELECT menu_categories.*, count(product_list.category_id) as 'total_products'
            FROM `menu_categories`
            left join product_list on product_list.category_id = menu_categories.category_id 
            WHERE menu_categories.deleted = 0
            GROUP BY menu_categories.category_id
            ORDER BY display_index ASC
        ";
        $stmt = $this->pdo->prepare($query);
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        http_response_code(200);
        echo json_encode($result);
    }

    public function post(){
        $query = "INSERT INTO menu_categories (category_id, name, display_index)
                VALUES (:setCategoryId, :setName, :setDisplayIndex)";

        $stmt = $this->pdo->prepare($query);
        $this->superBind("HAS BEEN ADDED", $stmt);
    }

    public function update($id){
        $query = "UPDATE menu_categories SET category_id = :setCategoryId, name = :setName, display_index = :setDisplayIndex WHERE category_id = :setid";
        if(!$this->model->validateId($id)){return;}
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $this->model->getId(), PDO::PARAM_STR);
        $this->superBind("HAS BEEN UPDATED", $stmt);
    }

    public function delete($id){
        $query = "UPDATE menu_categories SET deleted = true WHERE category_id = :setid";
        if(!$this->model->validateId($id)){return;}
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $this->model->getId(), PDO::PARAM_STR);
        $this->msg = "{$this->model->getId()} HAS BEEN DELETED";
        $this->execution->execute($stmt);

        $query = "UPDATE product_list SET deleted = true WHERE category_id = :setid";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $this->model->getId(), PDO::PARAM_STR);
        $this->msg = "{$this->model->getId()} HAS BEEN DELETED";
        $this->execution->execute($stmt);

        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }

    private function superBind($secondMessage, $stmt){
        if (!$this->model->validateFields()){return;}
        
        $stmt->bindValue(":setCategoryId", $this->model->getCategoryId(), PDO::PARAM_STR);
        $stmt->bindValue(":setName", $this->model->getName(), PDO::PARAM_STR);
        $stmt->bindValue(":setDisplayIndex", $this->model->getDisplayIndex(), PDO::PARAM_INT);

        $this->msg = "{$this->model->getName()} {$secondMessage}";
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }
}