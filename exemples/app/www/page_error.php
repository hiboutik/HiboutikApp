<?php

print 'An error has occured';
if ($app->error['error'] === 'no_account' or $app->error['error'] === 'no_timestamp' or $app->error['error'] === 'invalid_state' or $app->error['error'] === 'no_state') {
  print ': Login error<br>';
}
print $app->error['error_description'];
