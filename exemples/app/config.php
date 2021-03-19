<?php

/**
 * Configuration array
 */
$config = [
  'app' => [
    'error_page' => __DIR__.'/www/page_error.php'
  ],
  'db' => [
    'db_name' => 'my_database',
    'db_user' => 'my_db_user',
    'db_pass' => 'my_db_password'
  ],
  'oauth' => [
    'client_id' => 'my_oauth_client',
    'client_secret' => 'dfaj843rowe29ftsayodilfuraofhq3yqsda',
    'scope' => 'read_products write_products',// the separator is space (" ")
    'template_install' => __DIR__.'/oauth/page_installation.php',
    'template_result' => __DIR__.'/oauth/page_result_installation.php',
    'store_callback' => 'storeTokens',
    'delete_callback' => 'deleteTokens'
  ]
];
