<?php

require __DIR__.'/../app.php';

$result = $app->get("/brands/", ['p' => 2]);
if ($app->request_ok) {
  print_r($result);
} else {
  print_r($app->debug);
  if (!empty($app->error)) {
    print_r($app->error);
  }
}
