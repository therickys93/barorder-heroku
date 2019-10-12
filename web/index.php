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

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/views',
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

$api = $app['controllers_factory'];

$api->before(function (Request $request) use($app) {
  $auth = $request->headers->get("Authorization");
  $apikey = substr($auth, strpos($auth, ' '));
  $apikey = trim($apikey);
  if($apikey == getenv('AUTH_TOKEN')){
    if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
        $data = json_decode($request->getContent(), true);
        $request->request->replace(is_array($data) ? $data : array());
    }
  } else {
    $response = new stdClass();
    $response->success = false;
    return $app->json($response);
  }
});

$api->get('/products', function() use($app) {
  $st = $app['pdo']->prepare('SELECT * FROM public.product ORDER BY public.product.name');
  $st->execute();
  $products = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $products[] = $row['name'];
  }
  return $app->json($products);
});

$api->get('/productsWithPrice', function() use($app) {
  $st = $app['pdo']->prepare('SELECT * FROM public.product ORDER BY public.product.name');
  $st->execute();
  $products = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $product = new stdClass();
    $product->name = $row['name'];
    $product->price = floatval($row['price']);
    $products[] = $product;
  }
  return $app->json($products);
});

$api->get('/status', function() use($app){
  $response = new stdClass();
  $response->server = true;
  $response->database = true;
  $response->version = 'v0.1.0';
  return $app->json($response);
});

$api->post('/deleteProductAll', function() use($app){
  $response = new stdClass();
  $response->success = false;
  $st = $app['pdo']->prepare('DELETE FROM public.product');
  if($st->execute()){
    $response->success = true;
  }
  return $app->json($response);
});

$api->post('/insertProduct/{product}', function($product) use($app){
  $response = new stdClass();
  $response->success = false;
  $st = $app['pdo']->prepare('INSERT INTO public.product VALUES (?)');
  if($st->execute(array($product))){
    $response->success = true;
  }
  return $app->json($response);
});

$api->post('/insertProduct/{product}/{price}', function($product, $price) use($app){
  $response = new stdClass();
  $response->success = false;
  $st = $app['pdo']->prepare('INSERT INTO public.product VALUES (?, ?)');
  if($st->execute(array($product, floatval($price)))){
    $response->success = true;
  }
  return $app->json($response);
});

$api->post('/deleteProduct/{product}', function($product) use($app){
  $response = new stdClass();
  $response->success = false;
  $st = $app['pdo']->prepare('DELETE FROM public.product WHERE name = ?');
  if($st->execute(array($product))){
    $response->success = true;
  }
  return $app->json($response);
});

$api->post('/insertOrder', function (Request $request) use($app){
  $response = new stdClass();
  $response->success = false;
  $order = array(
      'id' => $request->request->get('id'),
      'table'  => $request->request->get('table'),
      'done' => 0,
      'price' => 0,
      'pay' => 0,
      'products' => $request->request->get('products')
  );
  $st = $app['pdo']->prepare('INSERT INTO public.order VALUES (?, ?, ?, ?, ?)');
  if($st->execute(array($order['id'], $order['table'], $order['done'], $order['pay'], $order['price']))){
    $st = $app['pdo']->prepare('INSERT INTO public.has_products VALUES (?, ?, ?)');
    $count = count($order['products']);
    for($i = 0; $i < $count; $i++){
      if($st->execute(array($order['id'], $order['products'][$i]['name'], $order['products'][$i]['quantity']))){
      } else {
        return $app->json($response);
      }
    }
    $response->success = true;
  }
  return $app->json($response);
});

$api->post('/completeOrder', function(Request $request) use($app){
  $response = new stdClass();
  $response->success = false;
  $order = array(
      'id' => $request->request->get('id')
  );
  $st = $app['pdo']->prepare('UPDATE public.order SET done = 1 WHERE public.order.id = ?');
  if($st->execute(array($order['id']))){
    $response->success = true;
  }
  return $app->json($response);
});

$api->post('/payOrder', function(Request $request) use($app){
  $response = new stdClass();
  $response->success = false;
  $order = array(
      'id' => $request->request->get('id')
  );
  $st = $app['pdo']->prepare('UPDATE public.order SET pay = 1 WHERE public.order.id = ?');
  if($st->execute(array($order['id']))){
    $st_delete_products = $app['pdo']->prepare('DELETE FROM public.has_products WHERE id = ?');
    $st_delete_products->execute(array($order['id']));
    $st_delete_order = $app['pdo']->prepare('DELETE FROM public.order WHERE id = ?');
    $st_delete_order->execute(array($order['id']));
    $response->success = true;
  }
  return $app->json($response);
});

$api->get('/orders', function() use($app){
  $st = $app['pdo']->prepare('SELECT id, public.order.table, done, pay, price FROM public.order WHERE id IN (SELECT id FROM public.order WHERE done = 0 AND pay = 0)');
  $st->execute();
  $ids = array();
  $orders = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $st_products = $app['pdo']->prepare('SELECT name, quantity FROM public.has_products WHERE id = ?');
    $st_products->execute(array($row['id']));
    $products = array();
    while($row_products = $st_products->fetch(PDO::FETCH_ASSOC)){
      $products[] = $row_products;
    }
    $row['products'] = $products;
    $orders[] = $row;
  }
  return $app->json($orders);
});

$api->get('/payments', function() use($app){
  $st = $app['pdo']->prepare('SELECT id, public.order.table, done, pay, price FROM public.order WHERE id IN (SELECT id FROM public.order WHERE done = 1 AND pay = 0)');
  $st->execute();
  $ids = array();
  $orders = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $st_products = $app['pdo']->prepare('SELECT name, quantity FROM public.has_products WHERE id = ?');
    $st_products->execute(array($row['id']));
    $products = array();
    while($row_products = $st_products->fetch(PDO::FETCH_ASSOC)){
      $products[] = $row_products;
    }
    $row['products'] = $products;
    $orders[] = $row;
  }
  return $app->json($orders);
});

$app->mount('/v1', $api);

$app->get('/', function() use($app) {
  $response = new stdClass();
  $response->success = true;
  return $app->json($response);
});

$app->get('/order/{order_id}', function($order_id) use($app){
  $st = $app['pdo']->prepare('SELECT id, public.order.table, done, pay, price FROM public.order WHERE id = ?');
  $st->execute(array($order_id));
  $ids = array();
  $orders = array();
  while ($row = $st->fetch(PDO::FETCH_ASSOC)) {
    $st_products = $app['pdo']->prepare('SELECT name, quantity FROM public.has_products WHERE id = ?');
    $st_products->execute(array($row['id']));
    $products = array();
    while($row_products = $st_products->fetch(PDO::FETCH_ASSOC)){
      $products[] = $row_products;
    }
    $row['products'] = $products;
    $orders[] = $row;
  }
  var_dump($orders);
  // return $app->json($orders);
  return $app['twig']->render('order.twig', array('order' => $orders));
});

$app->run();
