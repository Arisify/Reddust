<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\tile\Container;

class JukeboxHopperBehavior implements HopperBehavior{

    public function pull(HopperEntity $hopper, ?Container $above = null) : bool{
        // TODO: Implement pull() method.
    }

    public function push(HopperEntity $hopper, ?Container $facing = null) : bool{
        // TODO: Implement push() method.
    }
}