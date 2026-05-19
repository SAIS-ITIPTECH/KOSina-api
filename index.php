<?php

// HEADERS
$allowedDomains = [
    "https://sais-itiptech.github.io/KOSina-Dashboard/",
    "null", // local HTML files (file://)
    "http://localhost",
    "http://localhost:3000", // add whatever port you use
    "http://127.0.0.1",
];

$origin = $_SERVER["HTTP_ORIGIN"] ?? "";

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PATCH, DELETE, OPTIONS");
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
require_once __DIR__ . "/includes/modules/Misc/ConfirmPayment.php";
require_once __DIR__ . "/includes/modules/Misc/CountTotal.php";
require_once __DIR__ . "/includes/modules/LiveOrder/LiveOrder.php";

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
require_once __DIR__ . "/services/PayMongo/CheckoutSession.php";

// EXCEPTION HANDLER
set_exception_handler("ErrorHandler::handleException");

// ENV
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

class Main{ 
    private $table;
    private $id;
    private $datePage;
    private $page;
    private $tableMap = [
        "categories" => Categories::class,
        "products" => Products::class,
        "history" => History::class,
        "details" => Details::class,
        "neworder" => NewOrder::class,
        "image" => Image::class,
        "sales" => DailySales::class,
        "liveorder" => LiveOrder::class
    ];
    
    public function start(){
        error_log("start");
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
        $this->id = (isset($req[1]) && $req[1] !== "null") ? $req[1] : null;
        $this->datePage = (isset($req[2]) && $req[2] !== "null") ? $req[2] : null;
        $this->page = (isset($req[3]) && $req[3] !== "null") ? ((int) $req[3]) : null;
    }

    private function determine(){
        error_log("1");

        if($this->table == "login"){
            $login = new LoginController();
            $login->start();
            return null;

        } else if ($this->table == "return") {
            $return = new TokenChecker();
            $return->checkToken();
            return null;

        } else if ($this->table == "confirmCashless") {
            $confirmPaid = new ConfirmPayment();
            $confirmPaid->checkCashless();
            return null;

        } else if ($this->table == "confirmCash") {
            $confirmPaid = new ConfirmPayment();
            $confirmPaid->checkCash($this->getDbCrendentials(), $this->id);
            return null;

        } else if ($this->table == "checkpaid") {
            $checkPaid = new CheckoutSession(null, null, null);
            $checkPaid->checkPaid($this->id);
            return null;
        }

        error_log("2");

        // CONNECT DATABASE
        $dbCredentials = $this->getDbCrendentials();
        error_log(json_encode($dbCredentials));

        $database = new Database(getenv("DATABASE_HOSTNAME"), $dbCredentials["dbName"], $dbCredentials["dbUsername"], $dbCredentials["dbPassword"]);
        $pdo = $database->connectDatabase();
        $execution = new Execution();

        error_log("3");

        if ($this->id === "count") {
            $counter = new CountTotal($this->table, $pdo, $execution);
            $counter->count($this->datePage);
            return null;
        }

        $controller = $this->tableMap[$this->table] ?? null;
        if (!$controller) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => strtoupper("$this->table IS NOT A VALID HEADER")]);
            return null;
        }

        return new $controller($pdo, $execution, $this->id, $dbCredentials["role"], $this->datePage, $this->page, );
    }

    private function getDbCrendentials(){
        $tokenChecker = new TokenChecker();
        $token = $tokenChecker->decodeToken();
        $credentials = new CredentialsGraber($token);
        $credentials->connectCredentialsId();
        return ["dbName" => $credentials->getDbName(), "dbUsername" =>  $credentials->getDbUsername(), "dbPassword" =>  $credentials->getDbPassword(), "role" =>  $credentials->getRole()];
    }
}

$main = new Main;
$main->start();