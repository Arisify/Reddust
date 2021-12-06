<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Hopper as PmHopperTile;
use pocketmine\block\tile\Furnace;

use pocketmine\block\tile\Container;

//use pocketmine\block\tile\Tile;
use pocketmine\math\Facing;

//use pocketmine\inventory\Inventory;
use pocketmine\Server;

use pocketmine\block\Block;
//use pocketmine\block\utils\BlockDataSerializer;
//use pocketmine\block\utils\InvalidBlockStateException;
use pocketmine\item\Item;
//use pocketmine\math\AxisAlignedBB;

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
        $facing = $this->position->getWorld()->getTile($this->position->getSide($this->getFacing()));
        return $facing instanceof Container ? $facing->getInventory() : null;
    }

    protected function updateHopperTickers() : void{
        if($this->canRescheduleTransferCooldown()){
            $this->rescheduleTransferCooldown();
        }
    }

    public function readStateFromWorld() : void{
        parent::readStateFromWorld();
        $this->updateHopperTickers();
    }

    public function onNearbyBlockChange() : void{
        parent::onNearbyBlockChange();
        $this->updateHopperTickers();
    }

    public function canRescheduleTransferCooldown() : bool{
        return ($this->getContainerFacing() ?? $this->getContainerAbove()) !== null;
    }

    public function rescheduleTransferCooldown() : void {
        if ($this->transfer_cooldown < 0) {
            $this->transfer_cooldown = 0;
        }
    }

    protected function pull() : bool{
        $above = $this->getContainerAbove();
        $above_inventory = $above->getInventory();

        if (empty($above_inventory->getContents())) return false;
        foreach ($above_inventory->getContents() as $slot => $item) {
            if ($this->getInventory()->canAddItem($item)) {
                $above_inventory->setItem($slot, $item->pop());
                $this->getInventory()->addItem($item->setCount(1));
                return true;
            }
        }
        return false;
    }

    protected function push() : bool{
        $facing = $this->getContainerFacing();
        $facing_inventory = $facing->getInventory();
        $hopper_inventory = $this->getInventory();
        if (empty($hopper_inventory->getContents())) return false;

        foreach ($hopper_inventory->getContents() as $slot => $item) {
            if ($facing instanceof Furnace) {
                //assert($face != Facing::UP);
                if ($this->getFacing() == Facing::DOWN) {
                    $smelting = $facing_inventory->getSmelting();
                    if ($smelting === null || $item->equals($smelting)) {
                        $facing_inventory->setSmelting((new $item)->setCount(($smelting->getCount() ?? 0) + 1));
                        break;
                    }
                } else {
                    $fuel = $facing->getInventory()->getFuel();
                    if ($fuel->getCount() < $fuel->getMaxStackSize()) {
                        if ($fuel !== null ? $item->getFuelTime() > 0 && $fuel->equals($item) : $item->getFuelTime() > 0) {
                            $facing_inventory->setFuel((new $item)->setCount(($fuel->getCount() ?? 0) + 1));
                            break;
                        }
                    }
                }
                $hopper_inventory->setItem($slot, $item->pop());
            } else if ($facing_inventory->canAddItem($item)) {
                    $item = $hopper_inventory->getItem($slot);
                    $hopper_inventory->setItem($slot, $item->pop());
                    $facing_inventory->addItem($item->setCount(1));
            }
        }
    }

    public function onScheduledUpdate(): void {
        parent::onScheduledUpdate();
        if ($this->transfer_cooldown != 0) $this->transfer_cooldown--;
        if ($this->transfer_cooldown == 0) {
            $this->push();
            $this->pull();
        }
    }
}