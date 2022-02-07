<?php
declare(strict_types=1);
namespace arie\reddust\block;

use arie\reddust\block\behavior\hopper\ContainerHopperBehavior;
use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Container;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;

use arie\reddust\block\entity\HopperEntity;

class Hopper extends PmHopper {
	public const DEFAULT_COLLECTING_COOLDOWN = 8;
	public const DEFAULT_TRANSFERING_COOLDOWN = 8;
	/** @var int */
	protected int $collecting_cooldown = 0;

	/** @var int */
	protected int $transfering_cooldown = 0;

	public function readStateFromWorld() : void{
		parent::readStateFromWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		if ($tile instanceof HopperEntity) {
			$this->transfering_cooldown = max($tile->getTransferCooldown(), $this->transfering_cooldown);
		}
	}

	public function writeStateToWorld() : void{
		parent::writeStateToWorld();
		$tile = $this->position->getWorld()->getTile($this->position);
		assert($tile instanceof HopperEntity);
		$tile->setTransferCooldown($this->transfering_cooldown);
	}

	public function onNearbyBlockChange() : void{
		parent::onNearbyBlockChange();
		$this->reschedule();
	}

	public function onEntityLand(Entity $entity) : ?float{
		if ($entity instanceof ItemEntity) {
			$this->reschedule();
		}
		return parent::onEntityLand($entity);
	}

	public function hasEntityCollision() : bool{
		return true;
	}

	public function onEntityInside(Entity $entity) : bool{
		if (($entity instanceof ItemEntity)) {
			if ($this->collecting_cooldown <= 0) {
				if ($this->collect()) {
					$this->collecting_cooldown = self::DEFAULT_COLLECTING_COOLDOWN;
					$this->reschedule();
				}
			} else {
				$this->reschedule();
			}
		}
		return parent::onEntityInside($entity);
	}

	public function getCollectingBoxes() : array{
		return [
			AxisAlignedBB::one()
				->contract(3 / 16, 0, 3 / 16)
				->trim(Facing::DOWN, 10/16),
			AxisAlignedBB::one()
				->offset(0, 1, 0)
				->trim(Facing::UP, 0.25)
		];
	}

	public function getInventory() : ?HopperInventory{
		$tile = $this->position->getWorld()->getTile($this->position);
		return $tile instanceof HopperEntity ? $tile->getInventory() : null;
	}

	public function getContainerAbove() : ?Container{
		$above = $this->position->getWorld()->getTile($this->getPosition()->getSide(Facing::UP));
		return $above instanceof Container ? $above : null;
	}

	public function getContainerFacing() : ?Container{
		$facing = $this->position->getWorld()->getTile($this->position->getSide($this->getFacing()));
		return ($facing instanceof Container && $this->getFacing() !== Facing::UP) ? $facing : null;
	}

	public function reschedule() : void{
		if (!$this->position->getWorld()->isChunkLoaded($this->position->getX() >> 4, $this->position->getZ() >> 4)) {
			return;
		}
		$this->position->getWorld()->scheduleDelayedBlockUpdate($this->position, 1);
	}

	protected function collect() : bool{ //This has unknown case that makes hopper stop ticking when collecting items entity?
		$inventory = $this->getInventory();
		foreach ($this->getCollectingBoxes() as $collectBox) {
			foreach ($this->position->getWorld()->getNearbyEntities($collectBox->offset(
				$this->position->x,
				$this->position->y,
				$this->position->z
			)) as $entity) {
				if (!$entity instanceof ItemEntity || $entity->isClosed() || $entity->isFlaggedForDespawn()) {
					continue;
				}
				$item = $entity->getItem();
				for ($slot = 0; $slot < $inventory->getSize() && !$item->isNull(); ++$slot) {
					$s = $inventory->getItem($slot);

					if ($s->getCount() >= $s->getMaxStackSize()) {
						continue;
					}
					if ($s->canStackWith($item) || $s->isNull()) {
						$new_slot = min($item->getCount() + $s->getCount(), $item->getMaxStackSize());
						$inventory->setItem($slot, (clone $item)->setCount($new_slot));
                        $item->setCount($item->getCount() + $s->getCount() - $new_slot);
					}
				}
				if ($item->getCount() > 0) {
					$entity->setStackSize($item->getCount());
					unset($item);
					continue;
				}
				$entity->flagForDespawn();
				break;
			}
		}
		return isset($item);
	}

	public function onScheduledUpdate(): void {
		parent::onScheduledUpdate();

		$hopper = $this->position->getWorld()->getTile($this->position);
		if (!$hopper instanceof HopperEntity || $this->isPowered()) {
			return;
		}

		if ($this->transfering_cooldown <= 0) {
			$facing = $this->getContainerFacing();
			$above = $this->getContainerAbove();
			$facing_r = $facing !== null && (new ContainerHopperBehavior())->push($hopper, $facing);
			$above_r = $above !== null && (new ContainerHopperBehavior())->pull($hopper, $above);
			if ($facing_r || $above_r) {
				$this->transfering_cooldown = self::DEFAULT_TRANSFERING_COOLDOWN;
				$this->reschedule();

				foreach(Facing::ALL as $face) {
					$block = $this->position->getWorld()->getBlock($this->position->getSide($face));
					if ($block instanceof self) {
						$block->reschedule();
					}
				}
			}

		} else {
			$this->transfering_cooldown--;
			$this->reschedule();
		}

		if ($this->collecting_cooldown <= 0) {
			if ($this->collect()) {
				$this->collecting_cooldown = self::DEFAULT_COLLECTING_COOLDOWN;
				$this->reschedule();
			}
		} else {
			$this->collecting_cooldown--;
			$this->reschedule();
		}
	}
}
