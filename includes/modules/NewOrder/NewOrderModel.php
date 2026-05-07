<?php

require_once "./includes/Validation/Validation.php";
require_once "./includes/Database/Database.php";

class NewOrderModel {
private $dailySalesId;
private $orderId;
private $restoName;
private $paymentMethod;
private $orders;
private $totalPrice;
private $userInput;
private $validator;
private $id;

public function __construct(private $pdo, private $dailySalesQueryBuilder){
    $this->validator = new Validation();
    $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
        $this->userInput = json_decode(file_get_contents("php://input"), true) ?? [];
    }
}

public function checkDailySales(){
    $id = $this->dailySalesQueryBuilder->getLatest();
    if ($id != date("Ymd")) {
        $this->dailySalesId = date("Ymd");
        $this->dailySalesQueryBuilder->post($this->dailySalesId);
        return;
    }
    $this->dailySalesId = $id;
}

public function validateFields(){
    $restoName  = $this->restoNameValidator();
    if( !$restoName ) { return false; }
    $paymentMethod  = $this->paymentMethodValidator();
    if( !$paymentMethod ) { return false; }

    $this->restoName  = $restoName;
    $this->paymentMethod  = $paymentMethod;
    $this->OrderIdGenerator();

    return true;
}

public function OrderIdGenerator(){
    $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM order_history WHERE daily_sale_id = :setDailySalesId");
    $stmt->bindValue("setDailySalesId", $this->dailySalesId);
    $stmt->execute();
    $lastIndex = $stmt->fetchColumn();
    
    $date = date('Ymd');
    $autoIndex = str_pad($lastIndex + 1, 4 ,"0", STR_PAD_LEFT);
    $this->orderId = "$date-$autoIndex";
}

public function detailIdGenerator($count){
    $autoIndex = str_pad($count + 1, 4 ,"0", STR_PAD_LEFT);
    return "{$this->orderId}-$autoIndex";
}

public function validateId($dirtyId){
    $id = $this->idValidator($dirtyId);
    if(!$id) return false;
    $this->id = $id;
    return true;
}

public function validateOrders(){
    if (!isset($this->userInput["orders"])){
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => strtoupper("THERE ARE NO ORDERS!")]);
        return false;
    }

    foreach($this->userInput["orders"] as $index => $order){
        if(!$this->productIdValidator($order["productId"] ?? null)) { return false; }
        $this->userInput["orders"][$index]["price"] = $this->priceDB($order["productId"]);
        if(!$this->quantityValidator($order["quantity"] ?? null )) { return false; }
    }
    
    $this->totalPrice = $this->calculateTotalPrice();
    $this->orders = $this->userInput["orders"];
    return true;
}


private function calculateTotalPrice(){
    $totalPrice = 0;
    foreach($this->userInput["orders"] as $index => $order){
        $totalPrice += $order["price"] * $order["quantity"];
    }
    return $totalPrice;
}

private function restoNameValidator(){
    $value = $this->validator->checkEmpty("RESTORANT NAME", $this->userInput["restoName"] ?? null);
    if(isset($value)) { $value = $this->validator->checkSpecial("RESTORANT NAME", $value); }
    return $value;
}


private function paymentMethodValidator(){
    $value = $this->validator->checkEmpty("PAYMENT METHOD", $this->userInput["paymentMethod"] ?? null);
    if(isset($value)) { $value = $this->validator->checkSpecial("PAYMENT METHOD", $value); }
    return $value;
}

private function productIdValidator($value){
    $value = $this->validator->checkEmpty("PRODUCT ID", $value ?? null);
    if(isset($value)) { $value = $this->validator->checkNumber("PRODUCT ID", $value); }
    return $value;
}

private function priceDB($id){
    $query = "SELECT price FROM product_list WHERE product_id = :setid";
    $stmt = $this->pdo->prepare($query);
    $stmt->bindValue(":setid", $id);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['price'];
    return $result;
}

private function quantityValidator($value){
    $value = $this->validator->checkEmpty("QUANTITY", $value ?? null);
    if(isset($value)) { $value = $this->validator->checkNumber("QUANTITY", $value); }
    return $value;
}

private function idValidator($dirtyId){
    if(empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)){
        http_response_code(422);
        echo json_encode(["status" => "error", "message" => strtoupper("The item ID is invalid.")]);
        return false;
    }
    return $dirtyId;
}

public function getDailySalesId() { return $this->dailySalesId; }
public function getRestoname() { return $this->restoName; }
public function getOrderId(){ return $this->orderId; }
public function getOrders(){ return $this->orders; }
public function getTotalPrice(){ return $this->totalPrice; }
public function getPaymentMethod(){ return $this->paymentMethod; }
public function getId(){ return $this->id; }
}