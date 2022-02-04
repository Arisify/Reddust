<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\tile\Container;

class FurnaceHopperBehavior implements HopperBehavior{

	public function push(HopperEntity $hopper, ?Container $side = null) : bool{
	}

	public function pull(HopperEntity $hopper, ?Container $above = null) : bool{
		$item = $above_inventory->getResult();
		$inventory = $hopper->getInventory();
		if ((!$item->isNull()) && $hopper->getInventory()->canAddItem($item)) {
			$hopper->getInventory()->addItem($item->pop());
			$above_inventory->setResult($item);
			return true;
		}
		return false;
	}
}