<?php

session_start();


// Configuration file
require __DIR__.'/config.php';

/*------------------------------------------------------------------------------
 * For development. Delete these lines when in production!
 * Normally Hiboutik will send these $_GET parameters.
 */
$timestamp = date('U');
$state_hmac = hash_hmac('sha256', 'client_id='.$config['oauth']['client_id'].'&timestamp='.$timestamp, $config['oauth']['client_secret']);
$_GET['account'] = 'my_hiboutik_account';
$_GET['state'] = $state_hmac;
$_GET['timestamp'] = $timestamp;
/*----------------------------------------------------------------------------*/


// Dependencies (adapt according to your file structure or use Composer)
require __DIR__.'/../../HiboutikAPI/src/Hiboutik/HiboutikAPI/autoloader.php';
require __DIR__.'/../../HiboutikOAuthClient/src/Hiboutik/OAuth/autoloader.php';
// App
require __DIR__.'/../../HiboutikApp/src/Hiboutik/Apps/autoloader.php';

$account = Hiboutik\Apps\DefaultApp::getAccount();

// Get tokens from the database
$app_db = new Hiboutik\Apps\Db($config['db']['db_name'], $config['db']['db_user'], $config['db']['db_pass']);
$db_token = $app_db->getTokens($account);

$access_token = $db_token['access_token'];
$refresh_token = $db_token['refresh_token'];

// Configure OAuth
$oauth = new Hiboutik\OAuth\Client($account, $config['oauth']['client_id'], $config['oauth']['client_secret']);
$oauth->setScope($config['oauth']['scope']);
$oauth->template_install = $config['oauth']['template_install'];// path to php file or callback
$oauth->template_result = $config['oauth']['template_result'];// path to php file or callback

// Setup App
$app = new Hiboutik\Apps\DefaultApp();
$app->setAccessToken($access_token);
$app->setRefreshToken($refresh_token);
$app->setOAuthClient($oauth);
$app->store_callback = $config['oauth']['store_callback'];
$app->delete_callback = $config['oauth']['delete_callback'];


// Run application and check for errors
if (!$app->run()) {
  if (!empty($app->error)) {
    include $config['app']['error_page'];
  }
  exit;
}


// Callback for inserting new tokens in the database
function storeTokens($token, $app)
{
  global $app_db;
  global $account;
  $app_db->writeTokens($account, $token);
}


// Callback for deleting tokens from the database
function deleteTokens($app)
{
  global $app_db;
  global $account;
  $app_db->deleteTokens($account);
}
