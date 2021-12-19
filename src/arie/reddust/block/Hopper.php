<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\Jukebox;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Record;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;

use arie\reddust\block\tile\Hopper as HopperTile;

class Hopper extends PmHopper {

    /** @var int */
    protected int $collecting_cooldown = 0;
    /** @var int */
    protected int $transfering_cooldown = 0;

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

    public function reschedule() : void {
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    /** @noinspection NotOptimalIfConditionsInspection */

    protected function collect() : bool{
        $hopper_inventory = $this->getInventory();
        foreach ($this->getTile()->getCollectCollisionBoxes() as $collectCollisionBox) {
            foreach ($this->position->getWorld()->getNearbyEntities($collectCollisionBox) as $entity) {
                if ($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity instanceof ItemEntity) continue;
                $item = $entity->getItem();
                $amount = $item->getCount();
                // Optimize this for a better readable code
                for ($slot = 0; $slot < $hopper_inventory->getSize(); ++$slot) {
                    $s = $hopper_inventory->getItem($slot);

                    if ($s->isNull()) {
                        $ss = $item;
                    } elseif (!$s->canStackWith($item) || $s->getCount() === $s->getMaxStackSize()) continue;
                    else $ss = $s->isNull() ? $item : (clone $item)->setCount(min($s->getMaxStackSize(), $s->getCount() + $amount)); //Messed up bruh
                    $hopper_inventory->setItem($slot, $ss);
                    $amount -= $ss->getCount() - $s->getCount();
                    if ($amount <= 0) {
                        $amount = 0;
                        break;
                    }
                }

                if ($amount !== $item->getCount()) {
                    $entity->flagForDespawn();
                    if ($amount > 0) {
                        $this->position->getWorld()->dropItem($this->position, $item->setCount($amount), new Vector3(0, 0, 0));
                    }
                    return true;
                }
            }
        }
        return false;
    }

    protected function pull() : bool{
        $above = $this->getContainerAbove();
        $above_inventory = $above->getInventory();
        $hopper_inventory = $this->getInventory();

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
        $facing_inventory = $facing?->getInventory();
        $hopper_inventory = $this->getInventory();

        for ($slot = 0; $slot < $hopper_inventory->geTSize(); ++$slot) {
            $item = $hopper_inventory->getItem($slot);
            if ($item->isNull()) continue;

            $block = $this->position->getWorld()->getBlock($this->position->getSide(Facing::DOWN));

            if ($block instanceof Composter) {
                if ($block->getComposterFillLevel() < 8 && $block->compost($item->pop())) {
                    $hopper_inventory->setItem($slot, $item);
                    return true;
                }
                continue;
            }

            if ($block instanceof Jukebox) {
                if ($item instanceof Record) {
                    $block->insertRecord($item->pop());
                    $this->getInventory()->setItem($slot, $item);
                    $this->position->getWorld()->setBlock($block->getPosition(), $block);
                    return true;
                }
                continue;
            }

            if ($facing instanceof Furnace) {
                if ($this->getFacing() === Facing::DOWN) {
                    $smelting = $facing_inventory->getSmelting();
                    if ($smelting->isNull() || ($item->equals($smelting) && $smelting->getCount() < $smelting->getMaxStackSize())) {
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
                for ($slot2 = 0; $slot2 < $facing_inventory->getSize(); $slot2++) {
                    $slotItem = $facing_inventory->getItem($slot2);
                    if (!$slotItem->canStackWith($item) || $slotItem->getCount() === $slotItem->getMaxStackSize()) continue;

                    //$ss = $slotItem->isNull() ? $item->pop() : $item->pop()->setCount($slotItem->getCount() + 1);
;
                    $facing_inventory->setItem($slot, $item->pop()->setCount($slotItem->isNull() ? 1 : $slotItem->getCount() + 1));
                    $hopper_inventory->setItem($slot, $item);
                    break;
                }
                return true;
            }
        }
        return false;
    }

    public function onScheduledUpdate(): void {
        parent::onScheduledUpdate();
        if ($this->isPowered() || !$this->position->getWorld()->isChunkLoaded($this->position->getX() >> 4, $this->position->getZ() >> 4)) return;

        $this->transfering_cooldown--;
        $this->collecting_cooldown--;

        if ($this->transfering_cooldown <= 0 && $this->getInventory() !== null &&
            ((($block = $this->position->getWorld()->getBlock($this->position->getSide(Facing::DOWN))) instanceof Jukebox) || (($block = $this->position->getWorld()->getBlock($this->position->getSide(Facing::DOWN))) instanceof Composter) ||($this->getContainerFacing() ?? $this->getContainerAbove() !== null))
        ) {
            $facing = $this->getContainerFacing();
            if ($block instanceof Composter || $block instanceof Jukebox || $facing instanceof Container) {
                $this->push();
            }
            $above = $this->getContainerAbove();
            if ($above instanceof Container) {
                $this->pull();
            }

            $this->transfering_cooldown = 8;
        }

        if ($this->collecting_cooldown <= 0) {
            $this->collect();
            $this->collecting_cooldown = 8;
        }

        $this->reschedule();
    }
}