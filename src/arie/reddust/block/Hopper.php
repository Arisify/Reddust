<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace;
use pocketmine\block\tile\Hopper as PmHopperTile;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\player\Player;

class Hopper extends PmHopper {

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
        return ($facing instanceof Container && $this->getFacing() != Facing::UP) ? $facing : null;
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
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 8);
    }

    /**public function onBreak(Item $item, ?Player $player = null): bool {
        if (empty($this->getInventory()?->getContents())) {

        }
        return parent::onBreak($item, $player);
    }

    public function getDrops(Item $item): array{
        parent::getDrops();
    }*/

    protected function pull() : bool{
        $above = $this->getContainerAbove();
        $above_inventory = $above->getInventory();

        if ($above instanceof Furnace) {
            $item = $above_inventory->getResult();
            if ((!$item->isNull()) && $this->getInventory()->canAddItem($item)) {
                $this->getInventory()->addItem($item->pop());
                $above_inventory->setResult($item);
                return true;
            }
            return false;
        }

        for ($slot = 0; $slot < $above_inventory->getSize(); ++$slot) {
            $item = $above_inventory->getItem($slot);
            if ($item->isNull()) continue;
            if ($this->getInventory()->canAddItem($item)) {
                $this->getInventory()->addItem($item->pop());
                $above_inventory->setItem($slot, $item);
                return true;
            }
        }
        return false;
    }

    protected function push() : bool{
        $facing = $this->getContainerFacing();
        $facing_inventory = $facing->getInventory();
        $hopper_inventory = $this->getInventory();

        for ($slot = 0; $slot < $hopper_inventory->geTSize(); ++$slot) {
            $item = $hopper_inventory->getItem($slot);
            if ($item->isNull()) continue; //why :C
            if ($facing instanceof Furnace) {
                if ($this->getFacing() == Facing::DOWN) {
                    $smelting = $facing_inventory->getSmelting();
                    if ($smelting->isNull() || ($item->equals($smelting) && $smelting->getCount() < $smelting->getMaxStackSize())) { //Seems like $smelting is null is not really necessary.
                        $facing_inventory->setSmelting((clone $item)->setCount(($smelting->getCount() ?? 0) + 1));
                        $hopper_inventory->setItem($slot, $item->setCount($item->getCount() - 1));
                        return true;
                    }
                } else {
                    $fuel = $facing->getInventory()->getFuel();
                    if (!$fuel->isNull() ? $item->equals($fuel) && $fuel->getCount() < $fuel->getMaxStackSize() : $item->getFuelTime() > 0) {
                        $facing_inventory->setFuel((clone $item)->setCount(($fuel->getCount() ?? 0) + 1));
                        $hopper_inventory->setItem($slot, $item->setCount($item->getCount() - 1));
                        return true;
                    }
                }
            } else if (!$facing instanceof PmHopperTile && $facing_inventory->canAddItem($item)) {
                $facing_inventory->addItem($item->pop());
                $hopper_inventory->setItem($slot, $item);
                return true;
            }
        }
        return false;
    }

    public function onScheduledUpdate(): void {
        parent::onScheduledUpdate();
        if ($this->isPowered()) return;
        if ($this->getInventory() !== null){
            $facing = $this->getContainerFacing();
            if ($facing != null) {
                assert($facing instanceof Container);
                $this->push();
            }
            $above = $this->getContainerAbove();
            if ($above !== null) {
                assert($above instanceof Container);
                $this->pull();
            }
        }
        $this->updateHopperTickers();
    }
}