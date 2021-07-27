<?php

namespace Drupal\docmagic_migrate\Session;

use Drupal\Core\Session\AccountProxy as BaseAccountProxy;

class AccountProxy extends BaseAccountProxy
{
    public function __get($name)
    {
        // There appears to be a user "uid" reference called in PHP database records.
        if ('uid' == $name) {
            return $this->id();
        }
    }
}
