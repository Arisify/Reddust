<?php
declare(strict_types=1);

namespace arie\reddust\block\inventory;

use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;

class DispenserInventory extends DropperInventory{
	public function getWindowType() : int{
		return WindowTypes::DISPENSER;
	}


}