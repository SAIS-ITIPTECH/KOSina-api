<?php

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
require_once __DIR__ . "/includes/Database/CredentialsGrabber.php";
require_once __DIR__ . "/includes/Database/Database.php";
require_once __DIR__ . "/includes/Database/Execution.php";

// AUTHENTICATION
require_once __DIR__ . "/auth/LoginController.php";
require_once __DIR__ . "/auth/TokenChecker.php";

// SERVICES
require_once __DIR__ . "/services/PayMongo/CheckoutSession.php";

// EXCEPTION HANDLER
set_exception_handler("ErrorHandler::handleException");

// ENV
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->safeLoad();

class Main
{
    private string|null $table;
    private string|null $id;
    private string|null $datePage;
    private int|null $page;

    private array $tableMap = [
        "categories" => Categories::class,
        "products"   => Products::class,
        "history"    => History::class,
        "details"    => Details::class,
        "neworder"   => NewOrder::class,
        "image"      => Image::class,
        "sales"      => DailySales::class,
        "liveorder"  => LiveOrder::class,
    ];

    public function start(): void
    {
        $this->setTarget();
        $controller = $this->resolve();
        if ($controller === null) {
            return;
        }
        $controller->buildModel();
        $controller->query();
    }

    private function setTarget(): void
    {
        $path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);
        $segments = explode("/", trim($path, "/"));

        $this->table    = $segments[0] ?? null;
        $this->id       = (isset($segments[1]) && $segments[1] !== "null") ? $segments[1] : null;
        $this->datePage = (isset($segments[2]) && $segments[2] !== "null") ? $segments[2] : null;
        $this->page     = (isset($segments[3]) && $segments[3] !== "null") ? (int) $segments[3] : null;
    }

    private function resolve(): ?object
    {
        switch ($this->table) {
            case "login":
                (new LoginController())->start();
                return null;

            case "return":
                (new TokenChecker())->checkToken();
                return null;

            case "confirmCashless":
                (new ConfirmPayment())->checkCashless();
                return null;

            case "confirmCash":
                (new ConfirmPayment())->checkCash($this->getDbCredentials(), $this->id);
                return null;

            case "checkpaid":
                (new CheckoutSession(null, null, null))->checkPaid($this->id);
                return null;
        }

        $dbCredentials = $this->getDbCredentials();
        $database      = new Database(
            getenv("DATABASE_HOSTNAME"),
            $dbCredentials["dbName"],
            $dbCredentials["dbUsername"],
            $dbCredentials["dbPassword"]
        );
        $pdo       = $database->connectDatabase();
        $execution = new Execution();

        if ($this->id === "count") {
            (new CountTotal($this->table, $pdo, $execution))->count($this->datePage);
            return null;
        }

        $controllerClass = $this->tableMap[$this->table] ?? null;
        if ($controllerClass === null) {
            http_response_code(404);
            echo json_encode([
                "status"  => "error",
                "message" => strtoupper("{$this->table} IS NOT A VALID ENDPOINT"),
            ]);
            return null;
        }

        return new $controllerClass($pdo, $execution, $this->id, $dbCredentials["role"], $this->datePage, $this->page);
    }

    private function getDbCredentials(): array
    {
        $tokenChecker = new TokenChecker();
        $accountId    = $tokenChecker->decodeToken();
        $grabber      = new CredentialsGrabber($accountId);
        $grabber->connectById();

        return [
            "dbName"     => $grabber->getDbName(),
            "dbUsername" => $grabber->getDbUsername(),
            "dbPassword" => $grabber->getDbPassword(),
            "role"       => $grabber->getRole(),
        ];
    }
}

$main = new Main();
$main->start();
