<?php

/**
* Mapped to "sessions" collection
*/

use Phalcon\Mvc\Collection;

class Sessions extends Collection
{

    public function getSource()
    {
        return "sessions";
    }

}
