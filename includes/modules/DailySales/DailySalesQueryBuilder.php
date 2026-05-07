<?php
require_once "./includes/Database/Execution.php";
require_once "./includes/Database/Database.php";
require_once "./includes/Validation/Validation.php";

class DailySalesQueryBuilder{
    private $msg;

    public function __construct(
        private $pdo,
        private $execution
    ){}

    public function get(){
        $query = "SELECT * FROM daily_sales ORDER BY date ASC";
        $stmt = $this->pdo->prepare($query);
        $this->execution->execute($stmt);
        $result = $this->execution->getResults();
        http_response_code(200);
        echo json_encode($result);
    }

    public function post($dailySalesId){
        $query = "  INSERT INTO daily_sales ( daily_sale_id )
                    VALUES  ( :setDailySaleId );";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setDailySaleId",  $dailySalesId, PDO::PARAM_INT);
        $this->execution->execute($stmt);
    }

    public function update($dailySalesId, $totalPrice){
        $query = "UPDATE daily_sales SET total_income = total_income + :setTotalPrice, total_sales = total_sales + 1 WHERE daily_sale_id = :setid;";

        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $dailySalesId, PDO::PARAM_INT);
        $stmt->bindValue(":setTotalPrice", $totalPrice, PDO::PARAM_INT);
        $this->execution->execute($stmt);
    }

    public function delete($id){
        $validator = new Validation();
        if (!$validator->checkEmpty("ID", $id)) {return false;}
        if (!$validator->checkSpecial("ID", $id)) {return false;}

        $query = "DELETE FROM daily_sales WHERE daily_sale_id = :setid";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":setid", $id, PDO::PARAM_STR);
        $this->execution->execute($stmt);
        echo json_encode(["status" => "success", "message" => strtoupper($this->msg)]);
    }

    public function getLatest(){
        $query = "SELECT daily_sale_id FROM daily_sales ORDER BY date DESC LIMIT 1;";
        $stmt = $this->pdo->prepare($query);
        $this->execution->execute($stmt);
        return $this->execution->getResults()[0]["daily_sale_id"] ?? null;
    }

}