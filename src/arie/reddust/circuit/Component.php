<?php

namespace arie\reddust\circuit;

use pocketmine\block\Block;

class Component extends Block {

    public function getStrength() : int{
        return 0;
    }

    public function setStrength(int $strength) : int{
        return;
    }

    public function hasDirectPower() : bool{
        return false;
    }
}