<?php

/**
 * Very basic installation confirmation
 *
 * This page has in its namespaces two variables: '$_' and '$oauth'.
 * "$_" is an array with an URL:
 * <code>
 * [
 *   'page' => 'result',
 *   'result' => []
 * ];
 * </code>
 *
 * The "$oauth" variable is the "Hiboutik\OAuth\Client" object.
 */

print <<<HTML
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My app</title>
  </head>
  <body>
    <div>
HTML;

if (isset($_['result']['error'])) {
  print $_['result']['error_description'];
} else {
  print '<h1>Application instalée</h1>';
  print 'token: '.$_['result']['access_token'];
  print '<br><a href="home_page.php">Home</a>';
}

print <<<HTML
    </div>
  </body>
</html>
HTML;
