<?php

namespace Hiboutik\Apps;


class AppException extends \Exception
{
  public function __tostring()
  {
    return $this->getMessage();
  }
}
