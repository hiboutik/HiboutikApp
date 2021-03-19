<?php

require __DIR__.'/../app.php';

$result = $app->get("/brands/", ['p' => 1]);
// print_r($app->pagination());

$brands = [];
if ($app->request_ok) {
  $brands = $result;
} else {
//   print_r($app->debug);
  if (!empty($app->error)) {
    $status = $app->getStatus();// HTTP status
    $error = "HTTP status $status; ".$app->error['error'].': '.$app->error['error_description']
  } else {
    $error = "Unknown error";
  }
  die($error);
}





?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My app</title>
  </head>
  <body>
<?php

if ($app->request_ok) {
?>
    <h1>My brands</h1>
    <table>
      <tr>
        <th>Id</th>
        <th>Name</th>
        <th>Enabled</th>
        <th>Position</th>
      </tr>
<?php
  foreach ($brands as $brand) {
    print <<<HTML
      <tr>
        <td>{$brand['brand_id']}</td>
        <td>{$brand['brand_name']}</td>
        <td>{$brand['brand_enabled']}</td>
        <td>{$brand['brand_position']}</td>
      </tr>\n
HTML;
  }
?>
    </table>
    <a href="create_brand.php">Create a brand</a>
    <a href="modify_a_brand.php">Modify a brand</a>
<?php
} else {
  print $error_message;
}
?>
  </body>
</html>
