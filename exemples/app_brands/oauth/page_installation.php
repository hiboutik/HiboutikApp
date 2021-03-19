<?php

/**
 * Very basic user interface for installation
 *
 * This page has in its namespaces two variables: '$_' and '$oauth'.
 * "$_" is an array with an URL:
 * <code>
 * [
 *   'page'  => 'install',
 *   'url'    => 'https://my_account.hiboutik.com/api/oauth/authorize.php?response_type=code&client_id=1&state=fhaoisudfhsklakjdhf&scope=read_products$account=my_account&timestamp=1531754882',
 * ];
 * </code>
 * or an array with error informations:
 * <code>
 * [
 *   'page'  => 'install',
 *   'error' => 'invalid_token',
 *   'error_description' => 'Invalid token'
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

if (isset($_['error'])) {// User refused installation or an unsupported scope was requested
  print 'Error: '.$_['error_description'];
} else {
  print '<a class="bt mt-3" href="'.$_['url'].'">Installer</a>';
}

print <<<HTML
    </div>
  </body>
</html>
HTML;
