<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\BlockLegacyIds;
use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\Jukebox;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Container;
use pocketmine\block\tile\Furnace;
use pocketmine\block\tile\ShulkerBox;
use pocketmine\entity\object\ItemEntity;
use pocketmine\item\Record;
use pocketmine\math\Facing;

use arie\reddust\block\tile\Hopper as HopperTile;

class Hopper extends PmHopper {

    /** @var int */
    protected int $collecting_cooldown = 0;

    /** @var int */
    protected int $transfering_cooldown = 0;

    public function getCollectBoxes() : array{
        $tile = $this->position->getWorld()->getTile($this->position);
        return $tile instanceof HopperTile ? $tile->getCollectBoxes() : [];
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
        if ($this->getInventory() === null) return;
        $this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
    }

    /** @noinspection NotOptimalIfConditionsInspection */

    protected function collect() : bool{
        $hopper_inventory = $this->getInventory();
        foreach ($this->getCollectBoxes() as $collectBox) {
            foreach ($this->position->getWorld()->getNearbyEntities($collectBox) as $entity) {
                if ($entity->isClosed() || $entity->isFlaggedForDespawn() || !$entity instanceof ItemEntity) continue;
                $item = $entity->getItem();
                for ($slot = 0; $slot < $hopper_inventory->getSize() && !$item->isNull(); ++$slot) {
                    $s = $hopper_inventory->getItem($slot);

                    if ($s->getCount() >= $s->getMaxStackSize()) continue;
                    if ($s->canStackWith($item) || $s->isNull()) {
                        $new_slot = min($item->getCount() + $s->getCount(), $item->getMaxStackSize());
                        $hopper_inventory->setItem($slot, (clone $item)->setCount($new_slot));
                        $item->setCount($item->getCount() + $s->getCount() - $new_slot);
                    }
                }

                if ($item->isNull()) {
                    $entity->flagForDespawn();
                    return true;
                }
                if (($new_slot ?? 0) === $item->getMaxStackSize()) {
                    $entity->despawnFromAll();
                    $entity->spawnToAll();
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    protected function pull() : bool{
        $block = $this->position->getWorld()->getBlock($this->position->getSide(Facing::UP));
        if ($block instanceof Composter && $block->getComposterFillLevel() >= 8) $block->compost($this);

        $above = $this->getContainerAbove();
        if ($above === null) return false;

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

            for ($slot2 = 0; $slot2 < $hopper_inventory->getSize(); ++$slot2) {
                $slotItem = $hopper_inventory->getItem($slot2);
                if ($slotItem->isNull()) {
                    $hopper_inventory->setItem($slot2, $item->pop());
                    break;
                }

                if (!$slotItem->canStackWith($item) || $slotItem->getCount() === $slotItem->getMaxStackSize()) continue;

                $hopper_inventory->setItem($slot2, $item->pop()->setCount($slotItem->getCount() + 1));
                break;
            }
            $above_inventory->setItem($slot, $item);
            return true;
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    protected function push() : bool{
        $facing = $this->getContainerFacing();
        $facing_inventory = $facing?->getInventory();
        $hopper_inventory = $this->getInventory();

        $block = $this->position->getWorld()->getBlock($this->position->getSide($this->getFacing()));

        if (!$block instanceof Composter && !$block instanceof Jukebox && !$facing instanceof Container) return false;

        for ($slot = 0; $slot < $hopper_inventory->getSize(); ++$slot) {
            $item = $hopper_inventory->getItem($slot);
            if ($item->isNull()) continue;
            if ($facing instanceof ShulkerBox && ($item->getId() === BlockLegacyIds::UNDYED_SHULKER_BOX || $item->getId() === BlockLegacyIds::SHULKER_BOX)) continue;

            if ($block instanceof Composter) {
                if ($block->getComposterFillLevel() < 8) {
                    $block->compost($this, $item);
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
            } else {
                for ($slot2 = 0; $slot2 < $facing_inventory->getSize(); ++$slot2) {
                    $slotItem = $facing_inventory->getItem($slot2);
                    if ($slotItem->isNull()) {
                        $facing_inventory->setItem($slot2, $item->pop());
                        break;
                    }

                    if (!$slotItem->canStackWith($item) || $slotItem->getCount() === $slotItem->getMaxStackSize()) continue;

                    $facing_inventory->setItem($slot2, $item->pop()->setCount($slotItem->getCount() + 1));
                    break;
                }
                $hopper_inventory->setItem($slot, $item);
                return true;
            }
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public function onScheduledUpdate(): void {
        parent::onScheduledUpdate();
        if (!$this->position->getWorld()->isChunkLoaded($this->position->getX() >> 4, $this->position->getZ() >> 4) || $this->isPowered() || $this->getInventory() === null) {
            $this->reschedule();
            return;
        }

        $this->transfering_cooldown--;
        $this->collecting_cooldown--;

        if ($this->transfering_cooldown <= 0) {
            $this->push();
            $this->pull();
            $this->transfering_cooldown = 8;
        }

        if ($this->collecting_cooldown <= 0) {
            $this->collect();
            $this->collecting_cooldown = 8;
        }
        $this->reschedule();
    }
}