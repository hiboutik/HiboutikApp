<?php

namespace Hiboutik\Apps;
use \PDO;


class Db implements DbInterface
{
  /** @var object instance of the \PDO class */
  public $handle;

  /** @var string name of the database table where the tokens are stored */
  public $db_table_name = 'oauth_tokens';


/**
 * Default constructor
 *
 * @param string  $db      Database name
 * @param string  $db_user Database user
 * @param string  $db_pass Database password
 * @param string  $host
 * @param integer $port
 * @return void
 */
  public function __construct($db, $db_user, $db_pass, $host = 'localhost', $port = 3306)
  {
    $dsn = "mysql:dbname=$db; host=$host; port=$port; charset=utf8";
    $options = [
      PDO::ATTR_EMULATE_PREPARES   => false,
      PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION
    ];
    $this->handle = new PDO($dsn, $db_user, $db_pass, $options);
  }


/**
 * Get the token entry in the database
 *
 * @param string $account
 * @return array
 */
  public function getTokens($account = '')
  {
    $result = [];
    $statement = $this->handle->prepare("SELECT * FROM {$this->db_table_name} WHERE account = ?;");
    if ($statement->execute([$account])) {
      $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    }
    return [
      'access_token' => isset($result[0]) ? $result[0]['access_token'] : '',
      'refresh_token' => isset($result[0]) ? $result[0]['refresh_token'] : ''
    ];
  }


/**
 * Insert new token in the database
 *
 * Deletes the old token and inserts the new one
 *
 * @param string $account Hiboutik account
 * @param array  $token   Access and refresh tokens and additional info
 * @return boolean Returns the result from the insert query
 */
  public function writeTokens($account = '', $token = '')
  {
    $statement = $this->handle->prepare("DELETE FROM {$this->db_table_name} WHERE account = ?;");
    if (!$statement->execute([$account])) {
      return false;
    }
    $statement = $this->handle->prepare("
      INSERT INTO {$this->db_table_name} (
        account, access_token, expires_in, token_type, scope, refresh_token
      ) VALUES (
        ?, ?, ?, ?, ?, ?
      );");
    return $statement->execute([$account, $token['access_token'], $token['expires_in'], $token['token_type'], $token['scope'], $token['refresh_token']]);
  }


/**
 * Delete the token
 *
 * @param string $account Hiboutik account
 * @return boolean Returns the result from the delete query
 */
  public function deleteTokens($account = '')
  {
    $statement = $this->handle->prepare("DELETE FROM {$this->db_table_name} WHERE account = ?;");
    if (!$statement->execute([$account])) {
      return false;
    }
    return true;
  }


/**
 * Default table structure
 *
 * @return string MySQL table structure
 */
  public function createTable()
  {
    return "CREATE TABLE `$db_table_name` (
      `account` varchar(255) NOT NULL,
      `access_token` varchar(255) NOT NULL DEFAULT '',
      `expires_in` int(11) NOT NULL DEFAULT '600',
      `token_type` varchar(255) NOT NULL DEFAULT 'Bearer',
      `scope` text NOT NULL,
      `refresh_token` varchar(255) NOT NULL DEFAULT '',
      PRIMARY KEY (`access_token`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
  }
}
