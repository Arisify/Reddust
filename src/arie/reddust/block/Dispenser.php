<?php
declare(strict_types=1);

namespace arie\reddust\block;

use arie\reddust\block\entity\DispenserEntity;
use arie\reddust\block\inventory\DispenserInventory;

class Dispenser extends Dropper{
	public function getInventory() : ?DispenserInventory{
		$tile = $this->position->getWorld()->getTile($this->position);
		return $tile instanceof DispenserEntity ? $tile->getInventory() : null;
	}
}
