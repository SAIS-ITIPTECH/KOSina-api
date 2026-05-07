<?php

class ErrorHandler {
    public static function handleException(Throwable $exception) {
        http_response_code(500);

        if (true) {
            echo json_encode([
                "status" => "error",
                "message" => $exception->getMessage(),
                "line" => $exception->getLine(),
                "file" => $exception->getFile()
            ]);
        } else {
            
            echo json_encode([
                "status" => "error",
                "message" => "SOMETHING WENT WRONG"
            ]);
        }
    }
}