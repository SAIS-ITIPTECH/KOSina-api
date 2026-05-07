<?php
    class Execution{
        private $results;

        public function execute($stmt){
            $stmt->execute();
            $this->results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        public function getResults(){
            return $this->results;
        }

        public function sendMessage($message){
            echo json_encode($message);
        }
    }