<?php

class ProductQueryBuilder
{
    private string $msg = "";

    public function __construct(
        private ProductModel $model,
        private PDO          $pdo,
        private Execution    $execution
    ) {}

    public function get(?string $categoryId): void
    {
        $baseSelect = "
            SELECT product_list.product_id, product_list.name, product_list.category_id,
                   product_list.price, product_list.available,
                   product_images.display_url, product_images.image_id
            FROM product_list
            LEFT JOIN product_images ON product_list.product_id = product_images.product_id
            WHERE product_list.deleted = false
        ";

        if ($categoryId === null) {
            $stmt = $this->pdo->prepare($baseSelect);
        } else {
            $stmt = $this->pdo->prepare($baseSelect . " AND product_list.category_id = :setId");
            $stmt->bindValue(":setId", $categoryId, PDO::PARAM_STR);
        }

        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode($this->execution->getResults());
    }

    public function post(): void
    {
        $query = "INSERT INTO product_list (name, product_id, price, category_id, available)
                  VALUES (:setName, :setProductId, :setPrice, :setCategoryId, :setAvailable)";
        $stmt  = $this->pdo->prepare($query);
        $this->bindAndExecute($stmt, "HAS BEEN ADDED");
    }

    public function update(string $id): void
    {
        if (!$this->model->validateId($id)) {
            return;
        }
        $query = "UPDATE product_list
                  SET name = :setName, product_id = :setProductId, price = :setPrice, category_id = :setCategoryId, available = :setAvailable
                  WHERE product_id = :setId";
        $stmt  = $this->pdo->prepare($query);
        $stmt->bindValue(":setId", $this->model->getId(), PDO::PARAM_INT);
        $this->bindAndExecute($stmt, "HAS BEEN UPDATED");
    }

    public function delete(string $id): void
    {
        if (!$this->model->validateId($id)) {
            return;
        }
        $stmt = $this->pdo->prepare("UPDATE product_list SET deleted = true WHERE product_id = :setId");
        $stmt->bindValue(":setId", $this->model->getId(), PDO::PARAM_INT);
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => "DELETED SUCCESSFULLY"]);
    }

    private function bindAndExecute(PDOStatement $stmt, string $actionLabel): void
    {
        if (!$this->model->validateFields()) {
            return;
        }

        $stmt->bindValue(":setName",       $this->model->getName(),       PDO::PARAM_STR);
        $stmt->bindValue(":setProductId",       $this->model->getProductId(),       PDO::PARAM_STR);
        $stmt->bindValue(":setPrice",      $this->model->getPrice());
        $stmt->bindValue(":setCategoryId", $this->model->getCategoryId(), PDO::PARAM_STR);
        $stmt->bindValue(":setAvailable",  $this->model->getAvailable(),  PDO::PARAM_BOOL);

        $this->msg = strtoupper("{$this->model->getName()} {$actionLabel}");
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => $this->msg]);
    }
}
