<?php
declare(strict_types=1);
namespace arie\reddust\block\inventory;

use pocketmine\block\inventory\HopperInventory as PMHopperInventory;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;

class HopperInventory extends PMHopperInventory{
	public function push(Inventory $facing) : bool{
		foreach ($this->slots as $slot) {
			if ($slot === null) {
				continue;
			}
			for ($slot2 = 0; $slot2 < $facing->getSize(); ++$slot2) {
				$slotItem = $facing->getItem($slot2);
				if ($slotItem->isNull()) {
					$facing->setItem($slot2, $slot->pop());
					return true;
				}

				if (!$slotItem->canStackWith($slot) || $slotItem->getCount() >= $slotItem->getMaxStackSize()) {
					continue;
				}

				$facing->setItem($slot2, $slot->pop()->setCount($slotItem->getCount() + 1));
				return true;
			}
		}
		return false;
	}

	public function pull(Inventory $above) : bool{
		for ($slot = 0; $slot < $above->getSize(); ++$slot) {
			$item = $above->getItem($slot);
			if ($item->isNull()) {
				continue;
			}
			foreach ($this->slots as $slot) {
			}
		}
	}
}