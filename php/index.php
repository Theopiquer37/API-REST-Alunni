<?php
use Slim\Factory\AppFactory;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/controllers/AlunniController.php';
require __DIR__ . '/controllers/CertificazioniController.php';

$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
    return $response;
});

$app->get('/test', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Test page");
    return $response;
});

$app->get('/hello/{name}', function (Request $request, Response $response, array $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$controller = new AlunniController();

$app->get('/alunni', [$controller, 'index']);
$app->get('/alunni/{id}', [$controller, 'show']);
$app->post('/alunni', [$controller, 'create']);
$app->put('/alunni/{id}', [$controller, 'update']);
$app->delete('/alunni/{id}', [$controller, 'destroy']);

$certController = new CertificazioniController();

$app->get('/certificazioni', [$certController, 'index']);
$app->get('/certificazioni/{id}', [$certController, 'show']);
$app->post('/certificazioni', [$certController, 'create']);
$app->put('/certificazioni/{id}', [$certController, 'update']);
$app->delete('/certificazioni/{id}', [$certController, 'destroy']);

$app->run();
