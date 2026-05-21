<?php
require_once __DIR__ . "/../../Database/Execution.php";
require_once __DIR__ . "/../../Database/Database.php";
require_once __DIR__ . "/../../Validation/Validation.php";

class DailySalesQueryBuilder{
    private $msg;
    private $validator;

    public function __construct(
        private $pdo,
        private $execution
    ){
        $this->validator = new Validation;
    }

    public function get($datePage){
        if(!$this->datePageValidator($datePage)){return;}
        $slicedDate = explode("-", $datePage);
        $nextMonth  = ((int) $slicedDate[1]) + 1;
        $nextDate = "$slicedDate[0]-$nextMonth";
        $query = "
            SELECT * FROM daily_sales
            WHERE date >= :datePage
            AND date < :nextDate
            ORDER BY date DESC;
        ";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindValue(":datePage", "$datePage-01", PDO::PARAM_STR);
        $stmt->bindValue(":nextDate", "$nextDate-01", PDO::PARAM_STR);
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
        if(!$this->idValidator($id)){return;}
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

    private function datePageValidator($dirtyDatePage){
        if($this->validator->checkEmpty("DATE PAGE", $dirtyDatePage) === null) { return false; }
        if($this->validator->checkSpecial("DATE PAGE", $dirtyDatePage, '/[^a-zA-Z0-9\-]/') === null) { return false; }
        return true;
    }

    private function idValidator($dirtyId){
        if(empty($dirtyId) || !filter_var($dirtyId, FILTER_VALIDATE_INT)){
            http_response_code(422);
            echo json_encode(["status" => "error", "message" => strtoupper("The item ID is invalid.")]);
            return false;
        }
        if(!$this->validator->checkSpecial("CTATEGORY ID", $dirtyId, '/[^a-zA-Z0-9\-]/')) { return false; }
        return true;
    }
}
