<?php
declare(strict_types=1);

namespace arie\reddust\block;

use arie\reddust\block\inventory\DropperInventory;
use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\block\tile\Container;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\item\Item;
use pocketmine\math\Axis;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

use arie\reddust\block\entity\DropperEntity;

class Dropper extends Opaque {
    use PoweredByRedstoneTrait;

    private int $facing = Facing::NORTH;

    public function readStateFromData(int $id, int $stateMeta): void {
        $this->setFacing(BlockDataSerializer::readFacing($stateMeta & 0x07));
        $this->setPowered(($stateMeta & 0x08) !== 0);
    }

    protected function writeStateToMeta(): int {
        return BlockDataSerializer::writeFacing($this->facing) | ($this->isPowered() ? 0x08 : 0);
    }

    public function getStateBitmask() : int{
        return 0b1111;
    }

    public function getFacing() : int{ return $this->facing; }

    /** @return $this */
    public function setFacing(int $facing) : self{
        $this->facing = $facing;
        return $this;
    }

    public function getInventory() : ?DropperInventory{
        $tile = $this->position->getWorld()->getTile($this->position);
        return $tile instanceof DropperEntity ? $tile->getInventory() : null;
    }

    public function getContainerFacing() : ?Container{
        $facing = $this->position->getWorld()->getTile($this->position->getSide($this->facing));
        return $facing instanceof Container ? $facing : null;
    }

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        if ($player instanceof Player) {
            $dx = abs($player->getPosition()->getFloorX() - $this->position->x);
            $dy = $player->getPosition()->getFloorY() - $this->position->y;
            $dz = abs($player->getPosition()->getFloorZ() - $this->position->z);
            if ($dy > 0 && $dx < 2 && $dz < 2) {
                $this->facing = Facing::UP;
            } elseif ($dy < -1 && $dx < 2 && $dz < 2) {
                $this->facing = Facing::DOWN;
            } else {
                $this->facing = Facing::opposite($player->getHorizontalFacing());
            }
        }
        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }

    public function onNearbyBlockChange(): void{
        parent::onNearbyBlockChange();
        $this->ejectItem();
    }

    public function ejectItem() : bool{
        $inventory = $this->getInventory();
        $slot = $inventory->getRandomSlot();
        if ($slot === -1) {
            return false;
        }
        $item = $inventory->getItem($slot);
        $facing = $this->getContainerFacing();
        if ($facing !== null) {
            $facing_inventory = $facing->getInventory();
            for ($slot = 0; $slot < $facing_inventory->getSize(); ++$slot) {
                $slotItem = $facing_inventory->getItem($slot);
                if ($slotItem->isNull()) {
                    $facing_inventory->setItem($slot, $item->pop());
                    break;
                }

                if (!$slotItem->canStackWith($item) || $slotItem->getCount() >= $slotItem->getMaxStackSize()) {
                    continue;
                }

                $facing_inventory->setItem($slot, $item->pop()->setCount($slotItem->getCount() + 1));
                break;
            }
            $inventory->setItem($slot, $item); //TODO
            return true;
        }

        $v = (mt_rand(0, 100) / 1000 + 0.15) * (Facing::isPositive($this->facing) ? 1.0 : -1.0);
        $motion = new Vector3(
            mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($this->facing) === Axis::X ? 1.0 : 0.0) * $v,
            mt_rand(-100, 100) / 100 * 0.0075 * 6 + 0.15,
            mt_rand(-100, 100) / 100 * 0.0075 * 6 + (Facing::axis($this->facing) === Axis::Z ? 1.0 : 0.0) * $v,
        );
        $this->position->getWorld()->dropItem($this->position->add(0.5, 0.5, 0.5)->addVector(Vector3::zero()->getSide($this->facing)->multiply(0.55)), $item->pop(), $motion);
        $inventory->setItem($slot, $item);
        return true;
    }

    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        if($player instanceof PLayer){
            $tile = $this->position->getWorld()->getTile($this->position);
            if($tile instanceof DropperEntity){
                $player->setCurrentWindow($tile->getInventory());
            }
            return true;
        }
        return false;
    }
    //TODO: redstone logic, dropping logic
}
