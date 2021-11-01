<?php

namespace Tustin\PlayStation\Iterator\Filter\User;

use Iterator;
use FilterIterator;

class CloseFriendFilter extends FilterIterator
{
    public function __construct(Iterator $iterator)
    {
        parent::__construct($iterator);
    }

    public function accept()
    {
        return $this->current()->isCloseFriend() === true;
    }
}
