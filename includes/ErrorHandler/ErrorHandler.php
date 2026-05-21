<?php

class ErrorHandler
{
    public static function handleException(Throwable $exception): void
    {
        http_response_code(500);
        $message = $exception->getMessage();

        if (str_contains($exception->getMessage(), "1062")) {
            $message = self::duplicate($exception);
        }

        if (getenv("APP_ENV") === "development") {
            echo json_encode([
                "status"  => "error",
                "message" => $message,
                "line"    => $exception->getLine(),
                "file"    => $exception->getFile(),
            ]);
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => $message,
            ]);
        }
    }

    private static function duplicate($exception) {
        $slicedMessage = explode('\'', $exception->getMessage());
        $duplicate = $slicedMessage[1] ?? "unknown";
        $keyName = $slicedMessage[3] ?? "PRIMARY";
        $column = ($keyName === "PRIMARY") ? "ID" : strtoupper(explode('.', str_replace('_', " ", $keyName))[1]);
        return "THE $column '$duplicate' ALREADY EXISTS";
    }
}
