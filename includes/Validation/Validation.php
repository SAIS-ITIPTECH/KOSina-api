<?php

class Validation{
    public function checkEmpty($column, $value){
        if(!isset($value) || $value == ""){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("$column IS EMPTY!")]);
            return null;
        }
        return $value;
    }

    public function checkSpecial($column, $value, $match){
        if (!isset($value) || preg_match($match, $value)) {
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("$column CANT CONTAIN SPECIAL CHARACTERS!")]);
            return null;
        }
        return $value;
    }

    public function checkNumber($column, $value){
        var_dump("1", $value);
        if(!is_int($value)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("$column SHOULD BE A NUMBER!")]);
            return null;
        }
        return $value;
    }

    public function checkBoolean($column, $value){
        if($value != "true" && $value != "false"){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("$column SHOULD BE TRUE OR FALSE!")]);
            return null;
        }
        return $value;
    }

    public function checkLength($column, $value, $length){
        if(strlen($value) > $length){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("$column EXCEEDS THE MAXIMUM LENGTH")]);
            return null;
        }
        return $value;
    }
}