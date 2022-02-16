<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use pocketmine\block\Block;
use pocketmine\block\tile\Container;

use arie\reddust\block\entity\HopperEntity;

interface HopperBehavior{

	public function push(HopperEntity $hopper, Container|Block $facing) : bool;

	public function pull(HopperEntity $hopper, Container|Block $above) : bool;
}