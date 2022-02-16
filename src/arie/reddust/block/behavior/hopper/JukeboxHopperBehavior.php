<?php
declare(strict_types=1);

namespace arie\reddust\block\behavior\hopper;

use arie\reddust\block\entity\HopperEntity;
use pocketmine\block\Block;
use pocketmine\block\Jukebox;
use pocketmine\block\tile\Container;
use pocketmine\item\Record;

class JukeboxHopperBehavior implements HopperBehavior{

	public function push(HopperEntity $hopper, Block|Container $facing) : bool{
		assert($facing instanceof Jukebox);
		if ($facing->getRecord() !== null) {
			return false;
		}
		$hopper_inventory = $hopper->getInventory();
		for ($slot = 0; $slot < $hopper_inventory->getSize(); ++$slot) {
			$record = $hopper_inventory->getItem($slot);
			if ($record instanceof Record) {
				$facing->insertRecord($record->pop());
				$hopper_inventory->setItem($slot, $record);
				$hopper->getPosition()->getWorld()->setBlock($facing->getPosition(), $facing);
				return true;
			}
		}
		return false;
	}

	public function pull(HopperEntity $hopper, Block|Container $above) : bool{
		//NOOP
		return true;
	}
}
