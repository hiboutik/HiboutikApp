<?php

namespace Hiboutik\Apps;


interface DbInterface
{
  public function getTokens($account = '');
  public function writeTokens($account = '', $token = '');
}
