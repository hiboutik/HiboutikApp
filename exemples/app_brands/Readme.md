# Hiboutik Application Exemple

This package is a very basic Hiboutik app.

## Requirements

* PHP 5.3.0 or newer
* PHP cURL extension
* Hiboutik\HiboutikAPI
* Hiboutik\OAuth
* Hiboutik\Apps


## Structure

```
app
|
|>oauth
| |-page_installation.php
| |-page_result_installation.php
|>www
| |-page_error.php
| |-home_page.php
| |-create_brand.php
| |-modify_a_brand.php
|-app.php
|-config.php
```
### oauth
The 'oauth' directory contains the pages displayed when the app is installed and when the result of a token request is received. These pages must exist.
### www
The 'www' directory holds the public accessible pages. You can create all the pages you need here. You must have at least one page for your app.
When you register your application in Hiboutik you should set the redirect uri to a page in this directory and not to the app.php file.
The 'page_error.php' script is run when an error is encountered. You must always have this page.
This directory should be the only one publicly accessible.
### app.php
The 'app.php' script puts everything toghether. It must be included in the beginnig of every page you create in the 'www' directory.
### config.php
'config.php' is for configuring the app (obvious statement is obvious). It has three sections: 'app', 'db' and 'oauth'.
* app - specify the error page; this page is displayed when an oauth protocol error is encountered or the state or account are not valid
* db - database login credentials; this is optional as you can use your method of choice to deal with the tokens
* oauth - client credentials, scopes, templates for installing and request result and the callback for dealing with the token


## App setup

Start session
```php
session_start();
```

Include the configuration file:
```php
require __DIR__.'/config.php';
```

Include dependecies with composer:
```php
<?php
require 'vendor/autoload.php';
```
or manually:
```php
<?php
require '../HiboutikAPI/src/Hiboutik/HiboutikAPI/autoloader.php';
require '../HiboutikOAuthClient/src/Hiboutik/OAuth/autoloader.php';
require '../HiboutikApp/src/Hiboutik/Apps/autoloader.php';
```

Get the tokens from the database:
```php
$app_db = new Hiboutik\Apps\Db($config['db']['db_name'], $config['db']['db_user'], $config['db']['db_pass']);
$db_token = $app_db->getTokens(Hiboutik\Apps\DefaultApp::getAccount());
$access_token = $db_token['access_token'];
$refresh_token = $db_token['refresh_token'];
```

Configure the OAuth client:
```php
$oauth = new Hiboutik\OAuth\Client(Hiboutik\Apps\DefaultApp::getAccount(), $config['oauth']['client_id'], $config['oauth']['client_secret']);
$oauth->setScope($config['oauth']['scope']);
$oauth->template_install = $config['oauth']['template_install'];
$oauth->template_result = $config['oauth']['template_result'];
```

Setup the app:
```php
$app = new Hiboutik\Apps\DefaultApp();
$app->setAccessToken($access_token);
$app->setRefreshToken($refresh_token);
$app->setOAuthClient($oauth);
$app->store_callback = $config['oauth']['store_callback'];
```

Run the app:
```php
if (!$app->run()) {
  if (!empty($app->error)) {
    include $config['app']['error_page'];
  }
  exit;
}
```

Callback that deals with the tokens:
```php
function storeTokens($token, $app)
{
  global $app_db;
  $app_db->writeTokens(Hiboutik\Apps\DefaultApp::getAccount(), $token);
}
```

## Creating a page

Go to 'www' and create a new .php document (in our exemple - home_page.php).
Include the 'app.php' file:
```php
<?php

require __DIR__.'/../app.php';
```

Make your API requests and check for errors (basic exemple):
```php
$result = $app->get("/brands/", ['p' => 2]);
if ($app->request_ok) {
  print_r($result);
  print_r($app->pagination());
} else {
  if (!empty($app->error)) {
    print_r($app->error);
  }
}
```

If you have no output or no error is present you may check the debug messages:
```php
print_r($app->debug);
```
