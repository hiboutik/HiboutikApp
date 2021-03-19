<?php

require __DIR__.'/../app.php';


$barand_modified = false;
$my_error = '';
if (isset($_POST['modify_brand'])) {
  $result = $app->put("/brands/", [
    'brand_id' => $_POST['brand_id'],
    'brand_name' => $_POST['brand_name']
  ]);
  if ($app->request_ok) {
    $barand_modified = true;
  } else {
    if (!empty($app->error)) {
//     print_r($app->debug);
      $status = $app->getStatus();
      switch ($status['http_code']) {
        case '404':
          $my_error = 'Page not found';
          break;
        case '422':
          $my_error = 'Error(s):<ul>';
          foreach ($result['details'] as $error => $error_description) {
            $my_error .= "<li>$error: $error_description</li>";
          }
          $my_error .= '</ul>';
          break;
        default:
          $my_error = 'An unknown error has occured. We are sorry for the inconvenience. Please contact admin';
      }
    }
  }
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

if (isset($_POST['modify_brand'])) {
  if ($barand_modified) {
    print <<<HTML
    Brand {$_POST['brand_id']} modified!
    <a href="home_page.php">Back</a>
HTML;
  } else {
    print $my_error;
  }
}

print <<<HTML
    <form action="#" method="post">
      <label>
        Brand id:
        <input type="text" name="brand_id">
      </label><br>
      <label>
        Brand name:
        <input type="text" name="brand_name">
      </label>
      <button type="submit" name="modify_brand">Ok</button>
    </form>
    <a href="home_page.php">Cancel</a>
HTML;

?>
  </body>
</html>
