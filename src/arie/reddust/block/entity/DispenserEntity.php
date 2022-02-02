<?php
declare(strict_types=1);

namespace arie\reddust\block\entity;

use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

use arie\reddust\block\inventory\DispenserInventory;

class DispenserEntity extends DropperEntity{

	/** @var DispenserInventory */
	private DispenserInventory $inventory;

	public function __construct(World $world, Vector3 $pos){
		parent::__construct($world, $pos);
		$this->inventory = new DispenserInventory($this->position);
	}

	public function getDefaultName() : string{
		return "Dispenser";
	}

	/**
	 * @return DispenserInventory
	 */
	public function getInventory() : DispenserInventory{
		return $this->inventory;
	}

	/**
	 * @return DispenserInventory
	 */
	public function getRealInventory() : DispenserInventory{
		return $this->inventory;
	}

	protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
		//NOOP
	}
}
