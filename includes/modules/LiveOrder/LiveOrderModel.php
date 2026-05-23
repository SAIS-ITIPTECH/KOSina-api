<?php

require_once __DIR__ . "/../../Validation/Validation.php";

class LiveOrderModel {
    private $validator;

    public function __construct(){
        $this->validator = new Validation();
    }

    public function datePageValidator($dirtyDatePage){
        if($this->validator->checkEmpty("DATE PAGE", $dirtyDatePage) === null) { return false; }
        if($this->validator->checkSpecial("DATE PAGE", $dirtyDatePage, '/[^a-zA-Z0-9\-]/') === null) { return false; }
        return true;
    }

    public function pageValidator($dirtyPage){
        if($this->validator->checkEmpty("PAGE", $dirtyPage) === null) { return false; }
        if($this->validator->checkNumber("PAGE", $dirtyPage) === null) { return false; }
        return true;
    }

    public function totalValidator($dirtyTotal){
        if($this->validator->checkEmpty("TOTAL", $dirtyTotal) === null) { return false; }
        if($this->validator->checkNumber("TOTAL", $dirtyTotal) === null) { return false; }
        return true;
    }
}