<?php

//KakosaNiNina11
// HEADERS
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit;
}

// MODULES
require_once __DIR__ . "/includes/modules/Categories/Categories.php";
require_once __DIR__ . "/includes/modules/Products/Products.php";
require_once __DIR__ . "/includes/modules/Image/Image.php";
require_once __DIR__ . "/includes/modules/History/History.php";
require_once __DIR__ . "/includes/modules/Details/Details.php";
require_once __DIR__ . "/includes/modules/NewOrder/NewOrder.php";
require_once __DIR__ . "/includes/modules/DailySales/DailySales.php";

// TOOLS
require_once __DIR__ . "/includes/ErrorHandler/ErrorHandler.php";
require_once __DIR__ . "/includes/Database/CredentialsGraber.php";
require_once __DIR__ . "/includes/Database/Database.php";
require_once __DIR__ . "/includes/Database/Execution.php";

// AUTHENTICATION
require_once __DIR__ . "/auth/LoginController.php";
require_once __DIR__ . "/auth/TokenChecker.php";
require_once __DIR__ . "/auth/TokenChecker.php";

// SERVICES
require_once __DIR__ . "/services/PayMongo/ConfirmPaid.php";

// EXCEPTION HANDLER
set_exception_handler("ErrorHandler::handleException");

// ENV
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

class Main{ 
    private $table;
    private $id;
    private $tableMap = [
        "categories" => Categories::class,
        "products" => Products::class,
        "history" => History::class,
        "details" => Details::class,
        "neworder" => NewOrder::class,
        "image" => Image::class,
        "sales" => DailySales::class
    ];
    
    public function start(){
        $this->setTarget();
        $controller = $this->determine();
        if (!isset($controller)){return;}
        $controller->buildModel();
        $controller->query();
    }

    private function setTarget(){
        $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $req = explode("/", trim($path, "/"));
        $this->table = $req[0] ?? null;
        $this->id = $req[1] ?? null;
    }

    private function determine(){
        if($this->table == "login"){
            $login = new LoginController();
            $login->start();
            return null;

        } else if ($this->table == "return") {
            $return = new TokenChecker();
            $return->checkToken();
            return null;

        } else if ($this->table == "confirm") {
            $confirmPaid = new ConfirmPaid();
            $confirmPaid->checkPaid();
            return null;
        }

        $controller = $this->tableMap[$this->table] ?? null;
        if (!$controller){
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => strtoupper("$this->table IS NOT A VALID HEADER")]);
            return null;
        }

        // CONNECT DATABASE
        $dbCredentials = $this->GetDbCrendentials();
        $database = new Database(getenv("DATABASE_HOSTNAME"), $dbCredentials["dbName"], $dbCredentials["dbUsername"], $dbCredentials["dbPassword"]);
        $pdo = $database->connectDatabase();
        $execution = new Execution();

        return new $controller($this->id, $pdo, $execution);
    }

    private function GetDbCrendentials(){
        $tokenChecker = new TokenChecker();
        $token = $tokenChecker->decodeToken();
        $credentials = new CredentialsGraber($token);
        $credentials->connectCredentials("account_id");
        return ["dbName" => $credentials->getDbName(), "dbUsername" =>  $credentials->getDbUsername(), "dbPassword" =>  $credentials->getDbPassword()];
    }
}

$main = new Main;
$main->start();