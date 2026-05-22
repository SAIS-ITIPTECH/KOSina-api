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

        if (getenv("APP_ENV") === "localdev") {
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

    private static function duplicate(Throwable $exception) {
        $slicedMessage = explode('\'', $exception->getMessage());
        $duplicate = $slicedMessage[1] ?? "unknown";
        $keyName   = $slicedMessage[3] ?? "PRIMARY";

        if (str_contains($keyName, "PRIMARY")) {
            $column = "ID";
        } else {
            $keyName = explode('.', $keyName);
            $column  = strtoupper(str_replace('_', " ", end($keyName)));
        }

        return "THE $column '$duplicate' ALREADY EXISTS";
    }
}
