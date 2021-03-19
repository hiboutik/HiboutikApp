# Hiboutik Application

This package is a .

## Requirements

* PHP 5.3.0 or newer
* PHP cURL extension
* Hiboutik\HiboutikAPI
* Hiboutik\OAuth

## Installation

### Composer

```php
<?php
require 'vendor/autoload.php';
```

### Manual installation

Download this package along with 'Hiboutik/HiboutikAPI' and 'Hiboutik/OAuth'.

```php
require 'my/path/to/HiboutikAPI/src/Hiboutik/HiboutikAPI/autoloader.php';
require 'my/path/to/HiboutikOAuthClient/src/Hiboutik/OAuth/autoloader.php';
require 'my/path/to/HiboutikApp/src/Hiboutik/Apps/autoloader.php';
```

## Use

When viewed by a client from Hiboutik, the application will receive three parameters in $_GET:
* $_GET['account'] - hiboutik account of the user
* $_GET['state'] - a hash generated with the user's id, a timestamp and the clien's secret
* $_GET['timestamp'] - unix timestamp

This class automatically checks if the state is valid, insuring that the request came from Hiboutik.
The 'account' parameter is used to distinguish between different Hiboutik users who installed the application.

### Start session

```php
session_start();
```

### Get stored tokens

First, we need to get the stored tokens from a database or whatever you use.
This package contains a simple class to do this but you are free to use your own method.
```php
$app_db = new Hiboutik\Apps\Db($db_name, $db_user, $db_pass);
$db_token = $app_db->getTokens(Hiboutik\Apps\DefaultApp::getAccount());
$access_token = $db_token['access_token'];
$refresh_token = $db_token['refresh_token'];
```
Here is the table structure used:
```sql
CREATE TABLE `oauth_tokens` (
  `account` varchar(255) NOT NULL,
  `access_token` varchar(255) NOT NULL DEFAULT '',
  `expires_in` int(11) NOT NULL DEFAULT '600',
  `token_type` varchar(255) NOT NULL DEFAULT 'Bearer',
  `scope` text NOT NULL,
  `refresh_token` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`access_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

### Configure the OAuth client

```php
$oauth = new Hiboutik\OAuth\Client(Hiboutik\Apps\DefaultApp::getAccount(), $client_id, $client_secret);
$oauth->setScope($scope);
$oauth->template_install = 'templateInstall';// php script or callback
$oauth->template_result = 'templateResult';// php script or callback
```

### Setup the App

```php
$app = new Hiboutik\Apps\DefaultApp();
$app->setAccessToken($access_token);
$app->setRefreshToken($refresh_token);
$app->setOAuthClient($oauth);
$app->store_callback = 'storeCallback';
```
A callback function exemple to store the token:
```php
function storeTokens($token, $app)
{
  global $app_db;
  $app_db->writeTokens(Hiboutik\Apps\DefaultApp::getAccount(), $token);
}
```

### Run

```php
if (!$app->run()) {
  if (!empty($app->error)) {
    include 'path/to/error/page.php';
  }
  exit;
}

$result = $app->get("/brands/", ['p' => 2]);
if ($app->request_ok) {
  print_r($result);
} else {
  if (!empty($app->error)) {
    print_r($app->error);
  }
}
```

See 'exemples/app'.

