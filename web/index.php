<?php

require('../vendor/autoload.php');

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

$dbopts = parse_url(getenv('DATABASE_URL'));
$app->register(new Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider('pdo'),
               array(
                'pdo.server' => array(
                   'driver'   => 'pgsql',
                   'user' => $dbopts["user"],
                   'password' => $dbopts["pass"],
                   'host' => $dbopts["host"],
                   'port' => $dbopts["port"],
                   'dbname' => ltrim($dbopts["path"],'/')
                   )
               )
);

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('GET /');
  return 'Hello World';
});

$app->get('/v1/products', function() use($app) {
  $st = $app['pdo']->prepare('SELECT name FROM public.product');
  $st->execute();

  $names = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $app['monolog']->addDebug('Row ' . $row['name']);
    $names[] = $row['name'];
  }

  return $app->json($names);
});

$app->get('/v1/status', function() use($app){
  $response = new stdClass();
  $response->server = true;
  $response->database = true;
  $response->version = 'v0.1.0';
  return $app->json($response);
});

// $app->post('/v1/deleteProductAll', function() use($app){
//  $st = $app['pdo']->prepare('DELETE FROM public.product');
//  $st->execute();
// });

$app->run();
