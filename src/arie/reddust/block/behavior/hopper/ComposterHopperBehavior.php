<?php
declare(strict_types=1);
namespace arie\reddust\block\behavior\hopper;

use pocketmine\block\Block;
use pocketmine\block\tile\Container;

use arie\reddust\block\entity\HopperEntity;

class ComposterHopperBehavior implements HopperBehavior{
	public function push(HopperEntity $hopper, Block|Container $facing) : bool{
		//Todo: Finish this when Composter got added
		return false;
	}

	public function pull(HopperEntity $hopper, Block|Container $above) : bool{
		//Todo: Finish this when Composter got added
		return false;
	}
}
