<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use pocketmine\block\Block;
use pocketmine\block\tile\Container;

use arie\reddust\block\entity\HopperEntity;

class ContainerHopperBehavior implements HopperBehavior{

	public function push(HopperEntity $hopper, Block|Container $facing) : bool{
		assert($facing instanceof Container);
		$inventory = $hopper->getInventory();
		$facing_inventory = $facing->getInventory();
		for ($slot = 0; $slot < $inventory->getSize(); ++$slot) {
			$item = $inventory->getItem($slot);
			if ($item->isNull()) {
				continue;
			}

			for ($slot2 = 0; $slot2 < $facing_inventory->getSize(); ++$slot2) {
				$slotItem = $facing_inventory->getItem($slot2);
				if ($slotItem->isNull() || ($slotItem->canStackWith($item) && $slotItem->getCount() < $slotItem->getMaxStackSize())) {
					$facing_inventory->setItem($slot2, $item->pop()->setCount(max($slotItem->getCount(), 0) + 1));
					$inventory->setItem($slot, $item);
					return $item->getCount() >= 0;
				}
			}
		}
		return false;
	}

	public function pull(HopperEntity $hopper, Block|Container $above) : bool{
		assert($above instanceof Container);
		$inventory = $hopper->getInventory();
		$above_inventory = $above->getInventory();
		for ($slot = 0; $slot < $above_inventory->getSize(); ++$slot) {
			$item = $above_inventory->getItem($slot);
			if ($item->isNull()) {
				continue;
			}

			for ($slot2 = 0; $slot2 < $inventory->getSize(); ++$slot2) {
				$slotItem = $inventory->getItem($slot2);
				if ($slotItem->isNull() || ($slotItem->canStackWith($item) && $slotItem->getCount() < $slotItem->getMaxStackSize())) {
					$inventory->setItem($slot2, $item->pop()->setCount(max($slotItem->getCount(), 0) + 1));
					$above_inventory->setItem($slot, $item);
					return $item->getCount() >= 0;
				}
			}
		}
		return false;
	}
}
