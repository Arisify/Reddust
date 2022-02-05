<?php

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\ShulkerBox;
use pocketmine\inventory\Inventory;

class ContainerHopperBehavior implements HopperBehavior {

    public function push(HopperEntity $hopper, ?Inventory $facing = null) : bool{
		$inventory = $hopper->getInventory();
	    $facing_inventory = $facing->getInventory();
	    for ($slot = 0; $slot < $inventory->getSize(); ++$slot) {
		    $item = $inventory->getItem($slot);
		    if ($item->isNull()) {
			    continue;
		    }

		    if ($facing instanceof ShulkerBox && ($item->getId() === BlockLegacyIds::UNDYED_SHULKER_BOX || $item->getId() === BlockLegacyIds::SHULKER_BOX)) {
			    continue;
		    }

		    for ($slot2 = 0; $slot2 < $facing_inventory->getSize(); ++$slot2) {
			    $slotItem = $facing_inventory->getItem($slot2);

			    if (!$slotItem->canStackWith($item) || $slotItem->getCount() >= $slotItem->getMaxStackSize()) {
				    continue;
			    }

			    if ($slotItem->isNull()) {
				    $facing_inventory->setItem($slot2, $item->pop());
			    } else {
				    $facing_inventory->setItem($slot2, $item->pop()->setCount($slotItem->getCount() + 1));
			    }
			    $inventory->setItem($slot, $item);
				return true;
		    }
	    }
	    return false;
    }

	public function pull(HopperEntity $hopper, ?Container $above = null) : bool{
		$inventory = $hopper->getInventory();
		$above_inventory = $above->getInventory();
		for ($slot = 0; $slot < $above_inventory->getSize(); ++$slot) {
			$item = $above_inventory->getItem($slot);
			if ($item->isNull()) {
				continue;
			}

			for ($slot2 = 0; $slot2 < $inventory->getSize(); ++$slot2) {
				$slotItem = $inventory->getItem($slot2);
				if (!$slotItem->canStackWith($item) || $slotItem->getCount() >= $slotItem->getMaxStackSize()) {
					continue;
				}

				if ($slotItem->isNull()) {
					$inventory->setItem($slot2, $item->pop());
				} else {
					$inventory->setItem($slot2, $item->pop()->setCount($slotItem->getCount() + 1));
				}
				$above_inventory->setItem($slot, $item);
				return true;
			}
		}
		return false;
	}
}