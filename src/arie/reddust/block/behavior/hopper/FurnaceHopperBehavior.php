<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace;

class FurnaceHopperBehavior implements HopperBehavior{

	public function push(HopperEntity $hopper, ?Container $facing = null, bool $isSmelting = true) : bool{
		assert($facing instanceof Furnace);
		$inventory = $hopper->getInventory();
		$furnace_inventory = $facing->getInventory();
		$slotItem = $isSmelting ? $furnace_inventory->getSmelting() : $furnace_inventory->getFuel();
		if ($slotItem->getCount() >= $slotItem->getMaxStackSize()) {
			return false;
		}

		for ($slot = 0; $slot < $inventory->getSize(); ++$slot) {
			$item = $inventory->getItem($slot);
			if ($item->isNull() || (!$isSmelting && $item->getFuelTime() <= 0) || !$item->canStackWith($slotItem)) {
				continue;
			}
			if ($isSmelting) {
				$furnace_inventory->setSmelting($item->pop()->setCount(max($slotItem->getCount(), 0) + 1));
			} else {
				$furnace_inventory->setFuel($item->pop()->setCount(max($slotItem->getCount(), 0) + 1));
			}
			$inventory->setItem($slot, $item);
			return true;
		}
		return false;
	}

	public function pull(HopperEntity $hopper, ?Container $above = null) : bool{
		assert($above instanceof Furnace);
		$furnace_inventory = $above->getInventory();
		$result = $furnace_inventory->getResult();
		if ($result->isNull()) {
			return false;
		}
		$inventory = $hopper->getInventory();
		for ($slot = 0; $slot < $inventory->getSize(); ++$slot) {
			$slotItem = $inventory->getItem($slot);
			if ($slotItem->isNull() || ($slotItem->canStackWith($result) && $slotItem->getCount() <  $slotItem->getMaxStackSize())) {
				$inventory->setItem($slot, $result->pop()->setCount(max($result->getCount(), 0) + 1));
				$furnace_inventory->setResult($result);
				return true;
			}
		}
		return false;
	}
}