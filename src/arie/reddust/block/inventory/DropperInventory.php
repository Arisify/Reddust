<?php
declare(strict_types=1);

namespace arie\reddust\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\world\Position;

class DropperInventory extends SimpleInventory implements BlockInventory, IWindowType{
    use BlockInventoryTrait;

    public function __construct(Position $holder, int $size = 9){
        $this->holder = $holder;
        parent::__construct($size);
    }

    public function getWindowType(): int{
        return WindowTypes::DROPPER;
    }

	public function getRandomSlot0() : int{
		$slots = [];
		foreach ($this->slots as $k => $slot) {
			if (!$slot) {
				continue;
			}
			$slots[] = $k;
		}
		$count = count($slots);
		return $count > 0 ? $slots[mt_rand(0, $count - 1)] : -1;
	}

	public function getRandomSlot1() : int{
		$slots = [];
		foreach ($this->slots as $k => $slot) {
			if (!$slot) {
				continue;
			}
			$slots[] = $k;
		}
		return $slots ? $slots[mt_rand(0, count($slots) - 1)] : -1;
	}

	public function getRandomSlot2() : int{
		$slots = [];
		for ($slot = 0; $slot < $this->getSize(); ++$slot) {
			if (!$this->slots[$slot]) {
				continue;
			}
			$slots[] = $slot;
		}
		$count = count($slots);
		return $count > 0 ? $slots[mt_rand(0, $count - 1)] : -1;
	}

	public function getRandomSlot3() : int{
		$slots = [];
		for ($slot = 0; $slot < $this->getSize(); ++$slot) {
			if (!$this->slots[$slot]) {
				continue;
			}
			$slots[] = $slot;
		}
		return $slots ? $slots[mt_rand(0, count($slots) - 1)] : -1;
	}

	public function getRandomSlot4() : int{
		$slots = [];
		$i = -1;
		for ($slot = 0; $slot < $this->getSize(); ++$slot) {
			if (!$this->slots[$slot]) {
				continue;
			}
			$slots[++$i] = $slot;
		}
		return $i > -1 ? $slots[mt_rand(0, $i)] : -1;
	}

	public function getRandomSlot5() : int{
		$slots = [];
		$i = -1;
		foreach ($this->slots as $k => $slot) {
			if (!$slot) {
				continue;
			}
			$slots[++$i] = $k;
		}
		return $i > -1 ? $slots[mt_rand(0, $i)] : -1;
	}

	public function getRandomSlot6() : int{
		$slots = [];
		$i = -1;
		foreach ($this->slots as $k => $slot) {
			if ($slot === null) {
				continue;
			}
			$slots[++$i] = $k;
		}
		return $i > -1 ? $slots[mt_rand(0, $i)] : -1;
	}

}
