<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace;
use pocketmine\entity\object\ItemEntity;
use pocketmine\math\Facing;

use arie\reddust\block\tile\Hopper as HopperTile;
use pocketmine\math\Vector3;

class Hopper extends PmHopper {

    protected int $collect_cooldown = 0;
    protected int $transfer_cooldown = 0;

    public function getTile() : ?HopperTile{
        $tile = $this->position->getWorld()->getTile($this->position);
        return $tile instanceof HopperTile ? $tile : null;
    }

    public function getInventory() : ?HopperInventory{
        $tile = $this->position->getWorld()->getTile($this->position);
        return $tile instanceof HopperTile ? $tile->getInventory() : null;
    }

    public function getContainerAbove() : ?Container{
        $above = $this->position->getWorld()->getTile($this->getPosition()->getSide(Facing::UP));
        return $above instanceof Container ? $above : null;
    }

    public function getContainerFacing() : ?Container{
        $facing = $this->position->getWorld()->getTile($this->position->getSide($this->getFacing()));
        return ($facing instanceof Container && $this->getFacing() !== Facing::UP) ? $facing : null;
    }

    protected function updateHopperTickers() : void{
        if($this->canRescheduleTransferCooldown()){
            $this->rescheduleTransferCooldown();
        }
    }

    public function onNearbyBlockChange() : void{
        parent::onNearbyBlockChange();
        $this->updateHopperTickers();
    }

    public function canRescheduleTransferCooldown() : bool{
        return true; //($this->getContainerFacing() ?? $this->getContainerAbove()) !== null;
    }

    public function rescheduleTransferCooldown() : void {
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    public function collect() : void{
        $hopper_inventory = $this->getInventory();
        foreach ($this->getTile()->getCollectCollisionBoxes() as $collectCollisionBox) {
            foreach ($this->position->getWorld()->getNearbyEntities($collectCollisionBox) as $entity) {
                if ($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity instanceof ItemEntity) continue;
                $item = $entity->getItem();
                $amount = $item->getCount();
                //To-do: Optimize this for a better readable code
                for ($slot = 0; $slot < 5; ++$slot) { //$hopper_inventory->getSize(); ++$slot) {
                    $s = $hopper_inventory->getItem($slot);
                    if ($s->isNull()) {
                        $hopper_inventory->setItem($slot, $item);
                        $amount = 0;
                        break;
                    }
                    if (!$s->canStackWith($item) || $s->getCount() === $s->getMaxStackSize()) continue;
                    $s->setCount(min($s->getMaxStackSize(), $old = $s->getCount() + $amount));
                    $hopper_inventory->setItem($slot, $s);
                    //$amount -= max(0, $s->get);
                    $amount = $old - $s->getCount();
                    if ($amount <= 0) {
                        $amount = 0;
                        break;
                    }
                }

                //print("\n" . $item->getCount() . " ---> " . $amount . "\n");

                if ($amount !== $item->getCount()) {
                    $entity->flagForDespawn();
                    if ($amount > 0) {
                        $this->position->getWorld()->dropItem($this->position, $item->setCount($amount), new Vector3(0, 0, 0));
                    }
                }
            }
        }
    }

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
            if ($item->isNull()) continue;
            if ($facing instanceof Furnace) {
                if ($this->getFacing() === Facing::DOWN) {
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
            } elseif ($facing_inventory->canAddItem($item)) {
                $facing_inventory->addItem($item->pop());
                $hopper_inventory->setItem($slot, $item);
                return true;
            }
        }
        return false;
    }

    public function onScheduledUpdate(): void {
        parent::onScheduledUpdate();
        if ($this->isPowered() || !$this->position->getWorld()->isChunkLoaded($this->position->getX() >> 4, $this->position->getZ() >> 4)) return;

        $this->transfer_cooldown--;
        $this->collect_cooldown--;

        if ($this->transfer_cooldown === 0) {

            if ($this->getInventory() !== null && $this->getContainerFacing() ?? $this->getContainerAbove() !== null) {
                $facing = $this->getContainerFacing();
                if ($facing !== null) {
                    assert($facing instanceof Container);
                    $this->push();
                }
                $above = $this->getContainerAbove();
                if ($above !== null) {
                    assert($above instanceof Container);
                    $this->pull();
                }

                $this->transfer_cooldown = 8;
            }
        }

        if ($this->collecting_cooldown === 0) {
            $this->collect();
            $this->collecting_cooldown = 8;
        }

        $this->updateHopperTickers();
    }
}