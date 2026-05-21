<?php

class Validation
{
    public function checkEmpty(string $column, mixed $value): mixed
    {
        if (!isset($value) || $value === "") {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("{$column} IS EMPTY!")]);
            return null;
        }
        return $value;
    }

    public function checkSpecial(string $column, mixed $value, string $pattern): mixed
    {
        if (!isset($value) || preg_match($pattern, $value)) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("{$column} CANT CONTAIN SPECIAL CHARACTERS!")]);
            return null;
        }
        return $value;
    }

    public function checkNumber(string $column, mixed $value): mixed
    {
        if (!is_numeric($value)) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("{$column} SHOULD BE A NUMBER!")]);
            return null;
        }
        return $value;
    }

    public function checkBoolean(string $column, mixed $value): mixed
    {
        if ($value !== "true" && $value !== "false") {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("{$column} SHOULD BE TRUE OR FALSE!")]);
            return null;
        }
        return $value;
    }

    public function checkLength(string $column, mixed $value, int $length): mixed
    {
        if (strlen($value) > $length) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("{$column} EXCEEDS THE MAXIMUM LENGTH")]);
            return null;
        }
        return $value;
    }
}
