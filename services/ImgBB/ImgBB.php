<?php

require_once __DIR__ . "/../../vendor/autoload.php";

class ImgBB{
    public function upload($base64){
        $client = new \GuzzleHttp\Client();
        $response = $client->post('https://api.imgbb.com/1/upload', [
        'query' => [
            'key'        => $_ENV["IMGBB_KEY"],
        ],
        'multipart' => [
                [
                    'name'     => 'image',
                    'contents' => $base64
                ],
            ],
        ]);

        return json_decode($response->getBody(), true);
    }
}
