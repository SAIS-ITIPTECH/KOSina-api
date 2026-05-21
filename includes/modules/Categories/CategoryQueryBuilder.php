<?php

class CategoryQueryBuilder
{
    private string $msg = "";

    public function __construct(
        private CategoryModel $model,
        private PDO           $pdo,
        private Execution     $execution
    ) {}

    public function get(): void
    {
        $query = "
            SELECT menu_categories.*, COUNT(product_list.category_id) AS total_products
            FROM menu_categories
            LEFT JOIN product_list ON product_list.category_id = menu_categories.category_id
            WHERE menu_categories.deleted = 0 AND product_list.deleted = 0
            GROUP BY menu_categories.category_id
            ORDER BY display_index ASC
        ";
        $stmt = $this->pdo->prepare($query);
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode($this->execution->getResults());
    }

    public function post(): void
    {
        $query = "INSERT INTO menu_categories (category_id, name, display_index)
                  VALUES (:setCategoryId, :setName, :setDisplayIndex)";
        $stmt  = $this->pdo->prepare($query);
        $this->bindAndExecute("HAS BEEN ADDED", $stmt);
    }

    public function update(string $id): void
    {
        if (!$this->model->validateId($id)) {
            return;
        }
        $query = "UPDATE menu_categories
                  SET category_id = :setCategoryId, name = :setName, display_index = :setDisplayIndex
                  WHERE category_id = :setId";
        $stmt  = $this->pdo->prepare($query);
        $stmt->bindValue(":setId", $this->model->getId(), PDO::PARAM_STR);
        $this->bindAndExecute("HAS BEEN UPDATED", $stmt);
    }

    public function delete(string $id): void
    {
        if (!$this->model->validateId($id)) {
            return;
        }

        $categoryId = $this->model->getId();

        $stmt = $this->pdo->prepare("UPDATE menu_categories SET deleted = true WHERE category_id = :setId");
        $stmt->bindValue(":setId", $categoryId, PDO::PARAM_STR);
        $this->execution->execute($stmt);

        $stmt = $this->pdo->prepare("UPDATE product_list SET deleted = true WHERE category_id = :setId");
        $stmt->bindValue(":setId", $categoryId, PDO::PARAM_STR);
        $this->execution->execute($stmt);

        echo json_encode(["status" => "success", "message" => strtoupper("{$categoryId} HAS BEEN DELETED")]);
    }

    private function bindAndExecute(string $actionLabel, PDOStatement $stmt): void
    {
        if (!$this->model->validateFields()) {
            return;
        }
        $stmt->bindValue(":setCategoryId",   $this->model->getCategoryId(),   PDO::PARAM_STR);
        $stmt->bindValue(":setName",         $this->model->getName(),         PDO::PARAM_STR);
        $stmt->bindValue(":setDisplayIndex", $this->model->getDisplayIndex(), PDO::PARAM_INT);

        $this->msg = strtoupper("{$this->model->getName()} {$actionLabel}");
        $this->execution->execute($stmt);
        http_response_code(200);
        echo json_encode(["status" => "success", "message" => $this->msg]);
    }
}
