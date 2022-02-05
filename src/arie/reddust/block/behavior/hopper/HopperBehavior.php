<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Tile;
use pocketmine\inventory\Inventory;

interface HopperBehavior{
    public function push(HopperEntity $hopper, ?Container $facing = null) : bool;

	public function pull(HopperEntity $hopper, ?Container $above = null) : bool;
}