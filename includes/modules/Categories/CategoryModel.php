<?php

require_once __DIR__ . "/../../Validation/Validation.php";

class CategoryModel
{
    private string|null $categoryId   = null;
    private string|null $name         = null;
    private int|null    $displayIndex = null;
    private string|null $id           = null;
    private array       $userInput    = [];
    private Validation  $validator;

    public function __construct()
    {
        $this->validator = new Validation();
        $method = $_SERVER["REQUEST_METHOD"] ?? "GET";
        if (in_array($method, ["POST", "PUT", "PATCH", "DELETE"])) {
            $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
        }
    }

    public function validateFields(): bool
    {
        $categoryId   = $this->validateCategoryId();
        $name         = $this->validateName();
        $displayIndex = $this->validateDisplayIndex();

        if (!$categoryId || !$name || !$displayIndex) {
            return false;
        }

        $this->categoryId   = $categoryId;
        $this->name         = $name;
        $this->displayIndex = (int) $displayIndex;
        return true;
    }

    public function validateId(string $dirtyId): bool
    {
        if ($this->validator->checkEmpty("TARGET ID", $dirtyId ?? null) === null) {return false;}
        if ($this->validator->checkSpecial("TARGET ID", $dirtyId ?? null, '/[^a-zA-Z0-9 _\-.]/') === null) {return false;}
        
        $this->id = $dirtyId;
        return true;
    }

    private function validateCategoryId(): mixed
    {
        $value = $this->validator->checkEmpty("CATEGORY ID", $this->userInput["categoryId"] ?? null);
        if (isset($value)) {
            $value = $this->validator->checkSpecial("CATEGORY ID", $value, '/[^a-zA-Z0-9 _\-.]/');
        }
        return $value;
    }

    private function validateName(): mixed
    {
        return $this->validator->checkEmpty("NAME", $this->userInput["name"] ?? null);
    }

    private function validateDisplayIndex(): mixed
    {
        $value = $this->validator->checkEmpty("INDEX", $this->userInput["displayIndex"] ?? null);
        if (isset($value)) {
            $value = $this->validator->checkNumber("INDEX", $value);
        }
        return $value;
    }

    public function getCategoryId(): string|null   { return $this->categoryId; }
    public function getName(): string|null         { return $this->name; }
    public function getDisplayIndex(): int|null    { return $this->displayIndex; }
    public function getId(): string|null           { return $this->id; }
}
