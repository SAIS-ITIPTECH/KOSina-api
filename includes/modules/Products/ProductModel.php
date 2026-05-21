<?php

require_once __DIR__ . "/../../Validation/Validation.php";

class ProductModel
{
    private string|null $name       = null;
    private float|null  $price      = null;
    private string|null $categoryId = null;
    private bool|null   $available  = null;
    private int|null    $id         = null;
    private array       $userInput  = [];
    private Validation  $validator;

    public function __construct()
    {
        $this->validator = new Validation();
        $method          = $_SERVER["REQUEST_METHOD"] ?? "GET";
        if (in_array($method, ["POST", "PUT", "PATCH", "DELETE"])) {
            $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
        }
    }

    public function validateFields(): bool
    {
        $name       = $this->validateName();
        $price      = $this->validatePrice();
        $categoryId = $this->validateCategoryId();
        $available  = $this->validateAvailable();

        if (!$name || !$price || !$categoryId || $available === null) {
            return false;
        }

        $this->name       = $name;
        $this->price      = (float) $price;
        $this->categoryId = $categoryId;
        $this->available  = ($available === "true");
        return true;
    }

    public function validateId(string $dirtyId): bool
    {
        if (empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => "THE PRODUCT ID IS INVALID."]);
            return false;
        }
        $this->id = (int) $dirtyId;
        return true;
    }

    private function validateName(): mixed
    {
        $value = $this->validator->checkEmpty("NAME", $this->userInput["name"] ?? null);
        if (isset($value)) {
            $value = $this->validator->checkSpecial("NAME", $value, '/[^a-zA-Z0-9 _\-.]/');
        }
        return $value;
    }

    private function validatePrice(): mixed
    {
        $value = $this->validator->checkEmpty("PRICE", $this->userInput["price"] ?? null);
        if (isset($value)) {
            $value = $this->validator->checkNumber("PRICE", $value);
        }
        return $value;
    }

    private function validateCategoryId(): mixed
    {
        $value = $this->validator->checkEmpty("CATEGORY ID", $this->userInput["categoryId"] ?? null);
        if (isset($value)) {
            $value = $this->validator->checkSpecial("CATEGORY ID", $value, '/[^a-zA-Z0-9 _\-.]/');
        }
        return $value;
    }

    private function validateAvailable(): mixed
    {
        return $this->validator->checkEmpty("AVAILABLE", $this->userInput["available"] ?? null);
    }

    public function getName(): string|null    { return $this->name; }
    public function getPrice(): float|null    { return $this->price; }
    public function getCategoryId(): string|null { return $this->categoryId; }
    public function getAvailable(): bool|null { return $this->available; }
    public function getId(): int|null         { return $this->id; }
    public function getUserInput(): array     { return $this->userInput; }
}
