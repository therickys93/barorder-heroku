<?php

require('../vendor/autoload.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ParameterBag;

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

$app->before(function (Request $request) {
  if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
      $data = json_decode($request->getContent(), true);
      $request->request->replace(is_array($data) ? $data : array());
  }
});

$app->get('/', function() use($app) {
  $response = new stdClass();
  $response->success = true;
  return $app->json($response);
});

$app->get('/v1/products', function() use($app) {
  $st = $app['pdo']->prepare('SELECT name FROM public.product');
  $st->execute();

  $products = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $products[] = $row['name'];
  }

  return $app->json($products);
});

$app->get('/v1/status', function() use($app){
  $response = new stdClass();
  $response->server = true;
  $response->database = true;
  $response->version = 'v0.1.0';
  return $app->json($response);
});

$app->post('/v1/deleteProductAll', function() use($app){
  $response = new stdClass();
  $response->success = false;
  $st = $app['pdo']->prepare('DELETE FROM public.product');
  if($st->execute()){
    $response->success = true;
  }
  return $app->json($response);
});

$app->post('/v1/insertProduct/{product}', function($product) use($app){
  $response = new stdClass();
  $response->success = false;
  $st = $app['pdo']->prepare('INSERT INTO public.product VALUES (?)');
  if($st->execute(array($product))){
    $response->success = true;
  }
  return $app->json($response);
});

$app->post('/v1/deleteProduct/{product}', function($product) use($app){
  $response = new stdClass();
  $response->success = false;
  $st = $app['pdo']->prepare('DELETE FROM public.product WHERE name = ?');
  if($st->execute(array($product))){
    $response->success = true;
  }
  return $app->json($response);
});

$app->post('/v1/insertOrder', function (Request $request) use($app){
  $response = new stdClass();
  $response->success = false;
  $order = array(
      'id' => $request->request->get('id'),
      'table'  => $request->request->get('table'),
      'done' => $request->request->get('done')
  );
  $st = $app['pdo']->prepare('INSERT INTO public.order (id, table, done, pay) VALUES (?, ?, ?, ?)');
  if($st->execute(array($order->id, $order->table, $order->done, 0))){
    $response->success = true;
  }
  return $app->json($response);
});

$app->run();
