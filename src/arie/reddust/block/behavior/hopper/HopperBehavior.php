<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Container;

interface HopperBehavior{
    public function push(HopperEntity $hopper, ?Container $side = null) : bool;

	public function pull(HopperEntity $hopper, ?Container $above = null) : bool;
}