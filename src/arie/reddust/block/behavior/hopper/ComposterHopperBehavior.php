<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Container;

class ComposterHopperBehavior implements HopperBehavior{

    public function pull(HopperInventory $inventory, ?Container $above = null) : bool{
        // TODO: Implement pull() method.
    }

    public function push(HopperInventory $inventory, ?Container $facing = null) : bool{
        // TODO: Implement push() method.
    }
}