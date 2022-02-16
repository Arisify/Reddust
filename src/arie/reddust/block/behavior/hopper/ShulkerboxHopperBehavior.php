<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\Block;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\ShulkerBox;

class ShulkerboxHopperBehavior implements HopperBehavior{

	public function push(HopperEntity $hopper, Block|Container $facing) : bool{
		if ($facing instanceof ShulkerBox && ($item->getId() === BlockLegacyIds::UNDYED_SHULKER_BOX || $item->getId() === BlockLegacyIds::SHULKER_BOX)) {
			continue;
		}
	}

	public function pull(HopperEntity $hopper, Block|Container $above) : bool{
		// TODO: Implement pull() method.
	}
}