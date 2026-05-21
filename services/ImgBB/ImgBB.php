<?php

require_once __DIR__ . "/../../vendor/autoload.php";

class ImgBB
{
    public function upload(string $base64): array
    {
        $client   = new \GuzzleHttp\Client();
        $response = $client->post("https://api.imgbb.com/1/upload", [
            "query"     => ["key" => getenv("IMGBB_KEY")],
            "multipart" => [
                ["name" => "image", "contents" => $base64],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
