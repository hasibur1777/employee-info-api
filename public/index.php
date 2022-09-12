<?php

use App\Models\DB;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: X-Requested-With, Content-Type, Accept, Origin, Authorization');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, PATCH, OPTIONS');

$app = AppFactory::create();

$app->addRoutingMiddleware();
//$app->add(new BasePathMiddleware($app)); // extra test
$app->addErrorMiddleware(true, true, true);

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write('(get)/employee/all, (post)/employee/add, (put)/employee/update/{id}, (delete)/employee/{id}/delete');
    return $response;
});


// GET all employee
$app->get('/employee/all', function (Request $request, Response $response) {
    $sql = "SELECT * FROM employees WHERE is_active=1";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($customers));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


// GET single employee
$app->get('/employee/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM employees WHERE id='$id' AND is_active=1";

    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->query($sql);
        $customers = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($customers));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});


// POST add employee
$app->post('/employee/add', function (Request $request, Response $response, array $args) {
    $data = json_decode($request->getBody(), true); // $request->getParsedBody() cant return json format 
    $uid = $data["user_id"];
    $dept = $data["dept"];


    $sql = "INSERT INTO employees (user_id, dept) VALUES (:user_id, :dept)";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':user_id', $uid);
        $stmt->bindParam(':dept', $dept);

        $result = $stmt->execute();

        $db = null;
        $response->getBody()->write(json_encode($result));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});

// PUT update employee data
$app->put(
    '/employee/update/{id}',
    function (Request $request, Response $response, array $args) {
        $id = $request->getAttribute('id');
        $data = json_decode($request->getBody(), true);
        $uid = $data["user_id"];
        $dept = $data["dept"];

        $sql = "UPDATE employees SET user_id = :user_id, dept = :dept WHERE id = $id";

        try {
            $db = new DB();
            $conn = $db->connect();

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':user_id', $uid);
            $stmt->bindParam(':dept', $dept);

            $result = $stmt->execute();

            $db = null;
            echo "Update successful! ";
            $response->getBody()->write(json_encode($result));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200);
        } catch (PDOException $e) {
            $error = array(
                "message" => $e->getMessage()
            );

            $response->getBody()->write(json_encode($error));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
    }
);

//DELETE delete employee
$app->delete('/employee/{id}/delete', function (Request $request, Response $response, array $args) {
    $id = $args["id"];

    $sql = "DELETE FROM employees WHERE id = $id";
    //$sql = "UPDATE employees SET is_active = :dis WHERE id = $id";

    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt = $conn->prepare($sql);
        //$stmt->bindParam(':dis', 0);
        $result = $stmt->execute();

        $db = null;
        $response->getBody()->write(json_encode($result));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(200);
    } catch (PDOException $e) {
        $error = array(
            "message" => $e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
            ->withHeader('content-type', 'application/json')
            ->withStatus(500);
    }
});





$app->run();
