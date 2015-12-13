<?php

/**
* Mapped to "users" collection
*/

use Phalcon\Mvc\Collection;

class Users extends Collection
{

    public function getSource()
    {
        return "users";
    }

}
