<?php
declare(strict_types=1);

use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Hopper as PmHopperTile;

use pocketmine\block\tile\Container;

use pocketmine\block\tile\Tile;
use pocketmine\math\Facing;
class Hopper extends PmHopper {
    /** @var int */
    public readonly int $transfer_cooldown = 0;

    public function getInventory() : ?HopperInventory{
        $tile = $this->position->getWorld()->getTile($this->position);
        return $tile instanceof PmHopperTile ? $tile->getInventory() : null;
    }

    public function getContainerAbove() : ?Container{
        $above = $this->position->getWorld()->getTile($this->getPosition()->getSide(Facing::UP));
        return $above instanceof Container ? $above : null;
    }

    public function getContainerFacing() : ?Container{
        $facing = $this->position->getWorld()->getTile($this->position->getSide($this->facing));
        return $facing instanceof Container ? $facing->getInventory() : null;
    }

    //public function pull

    public function onScheduledUpdate(): void {
        parent::onScheduledUpdate();
        $hopper_inventory = $this->getInventory();

        if (!empty($hopper_inventory->getContents())) {
            $this->getContainerFacing();
        }
    }
}