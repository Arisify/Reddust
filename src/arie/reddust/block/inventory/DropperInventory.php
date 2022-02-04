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

	public function getRandomSlot() : int{
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
