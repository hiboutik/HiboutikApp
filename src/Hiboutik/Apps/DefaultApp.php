<?php

namespace Hiboutik\Apps;
use Hiboutik\HiboutikAPI;


/**
 * @package Hiboutik\Apps\DefaultApp
 *
 * @version 1.1.0
 * @author  Hiboutik
 *
 * @license GPLv3
 * @license https://gnu.org/licenses/gpl.html
 *
 */


/**
 * Hiboutik default application class
 *
 * Expects $_GET parameters 'account', 'state' and 'timestamp'. They are needed
 * to verify origin of request.
 */
class DefaultApp extends HiboutikAPI
{
  /** @var string */
  const GET_PARAM_STATE = 'state';
  /** @var string */
  const GET_PARAM_ACCOUNT = 'account';
  /** @var string */
  const GET_PARAM_LOCALE = 'locale';
  /**
   * Values:
   *   - invalid_state
   *   - no_state
   *   - no_timestamp
   *   - no_account
   *   - unknown_error
   *
   * @var array
   */
  public $error = [];
  /** @var array Additional info when available */
  public $debug = [];
    /** @var string Callback function to call when a new token is obtained */
  public $store_callback = null;
    /** @var string Callback function to call to delete the tokens */
  public $delete_callback = null;
  /** @var string */
  public $account = null;
  /** @var string */
  public $locale = 'fr';
  /** @var string */
  public $auth_query_string = null;
  /** @var string This key bypasses authentication */
  public $dev_key = '';

  /** @var string */
  protected $access_token = null;
  /** @var string */
  protected $refresh_token = null;
  /** @var object|null Hiboutik\OAuth\Client */
  protected $hoac = null;
  /**
   * @var array New token if one was obtained else empty
   *
   * <code>
   * [
   *   access_token => '18c148f580cb96ff458a0ec25c0e78b4a8bbf56d',
   *   expires_in => 16000000,
   *   token_type => 'Bearer',
   *   scope => 'basic_api',
   *   refresh_token => 'eef101f78656f404d79ea0ec877f9da12ae5c70d'
   * ]
   * </code>
   */
  protected $new_token = [];

  /** @var integer */
  private $counter = 0;



  public function __construct()
  {
  }


/**
 * @return string
 */
  public static function getAccount()
  {
    if (isset($_GET[self::GET_PARAM_ACCOUNT])) {
      return $_GET[self::GET_PARAM_ACCOUNT];
    }
    return null;
  }


/**
 * @return string
 */
  public static function getLocale($default = 'fr')
  {
    if (isset($_GET[self::GET_PARAM_LOCALE])) {
      return $_GET[self::GET_PARAM_LOCALE];
    }
    return $default;
  }


/**
 * @param string $token
 * @return DefaultApp
 */
  public function setAccessToken($token = '')
  {
    $this->access_token = $token;
    return $this;
  }


/**
 * @param string $token
 * @return DefaultApp
 */
  public function setRefreshToken($token = '')
  {
    if ($token != null) {
      $this->refresh_token = $token;
    }
    return $this;
  }


/**
 * @param object $hoac Hiboutik\Oauth\Client instance
 * @return DefaultApp
 */
  public function setOAuthClient($hoac)
  {
    $this->hoac = $hoac;
    return $this;
  }


/**
 * Auth with state, account and timestamp
 *
 * @return void
 */
  public function authState()
  {
    $this->debug[] = 'Hmac session auth';

    if (!isset($_GET['timestamp'])) {
      $this->debug[] = "Get parameter 'timestamp' not received";
      $this->_error('no_timestamp', "Missing get parameter 'timestamp'");
    } else {
      $this->debug[] = "Get parameter timestamp received";
    }
    if (!isset($_GET[self::GET_PARAM_STATE])) {
      $this->debug[] = "Get parameter 'state' missing";
      $this->_error('no_state', "Missing get parameter 'state'");
    } else {
      $this->debug[] = "Get parameter state received";
    }
    if (!empty($this->error)) {
      return false;
    }
    if (!$this->hoac->validSession()) {
      $this->debug[] = 'Invalid state';
      $this->_error('invalid_state', 'Invalid state');
    } else {
      $this->debug[] = 'State vaild, session opened';
      $this->auth_query_string = $this->hoac->authQueryString();
    }
  }


/**
 * Auth with dev key
 *
 * @return void
 */
  public function authDevKey()
  {
    $this->debug[] = 'Dev key auth';

    if (strlen($_GET['dev_key']) < 30) {
      $this->debug[] = 'dev_key too short; minimum length: 30 chars';
      $this->_error('invalid_dev_key_short', 'The developement key is too short, minimum length: 30 chars');
    }
    if (md5($this->dev_key) === md5($_GET['dev_key'])) {
      $this->auth_query_string = self::GET_PARAM_ACCOUNT.'='.$_GET[self::GET_PARAM_ACCOUNT].'&dev_key='.$_GET['dev_key'];
    } else {
      $this->debug[] = 'dev_key invalid';
      $this->_error('invalid_dev_key', 'The developement key is invalid');
    }
  }


/**
 * Auth with alternate method
 *
 * This method should be overriden to provide an alternate, secure
 * authentication.
 * It registres an error by default.
 * @return void
 */
  public function authAlternate()
  {
    $this->debug[] = 'An alternate authentication was attempted but the authAlternate method was not overwridden';
    $this->_error('auth', 'Authentication error');
  }


/**
 * Run app
 *
 * @return boolean
 */
  public function run()
  {
    $this->error = [];
    if (!isset($_GET[self::GET_PARAM_ACCOUNT])) {
      $this->debug[] = 'Account not set in $_GET or $_SESSION';
      $this->_error('no_account', "The '".self::GET_PARAM_ACCOUNT."' parameter is missing.");
      return false;
    } else {
      $this->debug[] = "Get parameter ".self::GET_PARAM_ACCOUNT." received: ".$_GET[self::GET_PARAM_ACCOUNT];
      $this->account = $_GET[self::GET_PARAM_ACCOUNT];
    }

    if (isset($_GET[self::GET_PARAM_LOCALE])) {
      $this->debug[] = "Get parameter ".self::GET_PARAM_LOCALE." received: ".$_GET[self::GET_PARAM_LOCALE];
      $this->locale = $_GET[self::GET_PARAM_LOCALE];
    } else {
      $this->debug[] = "Get parameter ".self::GET_PARAM_LOCALE." not received";
    }

    // Try dev_key
    if ($this->dev_key !== '' and isset($_GET['dev_key'])) {
      $this->authDevKey();
    } else if (isset($_POST['token'])) {
      $this->authAlternate();
    } else {
      $this->authState();
    }

    if (!empty($this->error)) {
      return false;
    }

    parent::__construct($this->account);

    // Check token
    if (isset($_GET['action']) and $_GET['action'] === 'install') {
      $this->debug[] = 'Installing application';
      $this->_installApp();
      return false;// show the installation confirmation, not the home page
    } else if ($this->access_token == false) {
      $this->debug[] = 'Access token is false';
      $this->_installApp();
      return false;// show the installation confirmation, not the home page
    } else {
      $this->debug[] = 'Token exists';
      if (isset($_GET['action']) and $_GET['action'] === 'uninstall') {// delete tokens (uninstall app)
        $this->debug[] = 'Uninstall app';
        if (is_callable($this->delete_callback)) {
          call_user_func($this->delete_callback, $this);
          return false;// do not show a page
        }
      }
      $this->oauth($this->access_token);
      return true;
    }
  }


/**
 * @internal
 *
 * @param string $store_callback Callable
 *
 * @return DefaultApp
 */
  protected function _installApp()
  {
    $this->hoac->run();
    $token = $this->hoac->showToken();
    if (!empty($token) and is_callable($this->store_callback)){
      $this->debug[] = 'Store token';
      call_user_func($this->store_callback, $token, $this);
    }
  }


/**
 * @param string $store_callback Callable
 *
 * @return DefaultApp
 */
  public function storeTokenCallback($store_callback)
  {
    $this->store_callback = $store_callback;
    return $this;
  }


/**
 * Check if an app is installed
 *
 * @param string $client_id OAuth client (app name)
 * @return void
 */
  public function checkInstallation($client_id)
  {
    $result = $this->get('/apps/installed/'.urlencode($client_id));
    if ($this->request_ok) {
      if (isset($result[$client_id])) {
        if ($result[$client_id] == 0) {
          return false;
        }
      } else {
        return false;
      }
    } else {
      return false;
    }
    return true;
  }




/**
 * @internal
 *
 * Overrides method from parrent class HiboutikAPI
 *
 * @param string $result JSON data object
 * @return array|string
 */
  protected function _handleRequest($result)
  {
    $this->request_ok = false;
    $code = $this->hr->getCode();
    $response = json_decode($result, true);
    if ($code === 200 or $code === 201) {
      $this->debug[] = 'HTTP request ok';
      $this->request_ok = true;
    } else {
      $this->debug[] = 'HTTP request error';
      $this->debug[] = $response;
      if (isset($response['error'])) {
        if ($response['code'] == 1) {// Authentication error. The token doesn't work, get a new one
          $this->debug[] = 'Access token not valid';
          if ($this->counter++ > 4) {
            $this->debug[] = 'Recursive error when getting the access token';
            $this->_error('recursive_error', 'This App has encountered an error that causes it to never reach the end of execution. Its execution has been stopped after '.$this->counter.' tries');
            return;
          }
          if ($this->refresh_token === null) {
            $this->debug[] = 'The refresh token is missing';
            $this->_error('refresh_token_missing', 'Cannot get a new token because a refresh token was not provided');
            return;
          }

          $access_token_refresh = $this->hoac->getRefreshToken($this->refresh_token);
          // If the access token is invalid, try getting a new token
          if (isset($access_token_refresh['error'])) {// bad refresh token
            $this->debug[] = 'The refresh token is not valid';
            // Get a new access token
            $this->debug[] = 'Getting a new access token';
            $this->hoac->run();
            $access_token = $this->hoac->showToken();
            if (!empty($access_token) and !isset($access_token['error'])) {
              $this->debug[] = 'Got a new access token';
              $this->new_token = $access_token;
              if (is_callable($this->store_callback)) {
                $this->debug[] = 'Store the new token';
                call_user_func($this->store_callback, $access_token, $this);
              }
            } else {
              $this->debug[] = 'Error getting a new access token';
              $this->debug[] = $access_token_refresh;
            }
          } else {// The refresh token works, a new access token has been obtained
            $this->debug[] = 'Refresh token ok';
            $this->new_token = $access_token_refresh;
            $this->oauth($access_token_refresh['access_token']);
            $this->access_token = $access_token_refresh['access_token'];
            if (is_callable($this->store_callback)){
              $this->debug[] = 'Store new access token';
              call_user_func($this->store_callback, $access_token_refresh, $this);
            }
            $this->debug[] = 'Repeat HTTP request';
            return $this->repeat();// last HTTP request is executed again for the second time
          }
        } else {
          $this->debug[] = 'An error occured when making an HTTP request';
          $this->debug[] = $response['error'].': '.$response['error_description'];
          $this->_error($response['error'], $response['error_description']);
        }
      } else {
          $this->debug[] = 'An unknown error occured; there is something wrong with the OAuth server maybe';
          $this->_error();
      }
    }
    return $response;
  }


/**
 * @internal
 *
 * @param string $error
 * @param string $error_description
 * @return void
 */
  protected function _error($error = 'unknown_error', $error_description = 'Unknown error')
  {
    $this->error[] = [
      'error' => $error,
      'error_description' => $error_description
    ];
  }
}
