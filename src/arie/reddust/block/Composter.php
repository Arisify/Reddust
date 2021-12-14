<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Transparent;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\particle\HappyVillagerParticle;

use arie\reddust\block\utils\ComposterUtils;
use arie\reddust\world\sound\ComposterEmptySound;
use arie\reddust\world\sound\ComposterFillSound;
use arie\reddust\world\sound\ComposterFillSuccessSound;
use arie\reddust\world\sound\ComposterReadySound;

class Composter extends Transparent {

    /** @var int */
    protected int $composter_fill_level = 0;

    protected function writeStateToMeta() : int{
        return $this->composter_fill_level;
    }

    public function writeStateToItemMeta(): int {
        return $this->composter_fill_level;
    }
    public function readStateFromData(int $id, int $stateMeta) : void{
        $this->composter_fill_level = BlockDataSerializer::readBoundedInt("composter_fill_level", $stateMeta, 0, 8);
    }

    public function getStateBitmask() : int{
        return 0b1111;
    }

    protected function recalculateCollisionBoxes() : array{ //Ban dau thi co 14 o trong, khi muc 1 la 13, muc 2 la 11, 3-9, 4-7, 5-5, 6-3, 7-1, 8-1
        $boxes = [$this->getSideCollisionBox(Facing::DOWN)];
        foreach (Facing::HORIZONTAL as $side) {
            $boxes[] = $this->getSideCollisionBox($side);
        }
        return $boxes;
    }

    protected function getSideCollisionBox(int $face = Facing::NORTH) : ?AxisAlignedBB{
        $empty = abs(15 - 2*$this->composter_fill_level) - ($this->composter_fill_level === 0);
        if ($face === Facing::DOWN) return AxisAlignedBB::one()->trim(Facing::UP, $empty/16);
        return AxisAlignedBB::one()->trim(Facing::opposite($face), 14/16)->trim(Facing::DOWN, 1 - ($empty/16));
    }

    /**
     * @throws \Exception
     */
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool{
        if ($player instanceof Player && !$player->isSneaking()) {
            if ($this->compost($item)) $item->pop();
        }
        return true;
    }

    /**
     * @throws \Exception
     */
    public function compost(Item $item) : bool{
        if ($this->composter_fill_level >= 8) {
            $this->position->getWorld()->addSound($this->position, new ComposterEmptySound());
            for ($i = 0; $i < 40; $i++) $this->position->getWorld()->addParticle($this->position->add(0.5 - sin(deg2rad(mt_rand(-75, 75))) / 2, 0.5 + mt_rand(-1, 10) / 16, 0.5 - sin(deg2rad(mt_rand(-75, 75))) / 2), new HappyVillagerParticle());
            $block = $this->position->getWorld()->getBlock($this->position->getSide(Facing::DOWN));
            if ($block instanceof Hopper) {
                $block->getInventory()->addItem((new Item(new ItemIdentifier(351, 15));
            } else {
                $this->position->getWorld()->dropItem($this->position->add(0.5, ($this->composter_fill_level + 2) / 16, 0.5), (new Item(new ItemIdentifier(351, 15), "Bone Meal"))->setCount(1), new Vector3(0, 0, 0));
            }
            $this->composter_fill_level = 0;
        } else {
            if (!ComposterUtils::isCompostable($item)) return false;
            $percent = ComposterUtils::getPercentage($item);
            if (mt_rand(1, 100) <= $percent) {
                ++$this->composter_fill_level;
                if ($this->composter_fill_level === 8) {
                    $this->position->getWorld()->addSound($this->position, new ComposterReadySound());
                } else {
                    $this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
                }
                for ($i = 0; $i < 30; $i++) $this->position->getWorld()->addParticle($this->position->add(0.5 - sin(deg2rad(mt_rand(-75, 75))) / 2, ($this->composter_fill_level + mt_rand(-1, 7)) / 16, 0.5 - sin(deg2rad(mt_rand(-75, 75))) / 2), new HappyVillagerParticle());
            } else {
                $this->position->getWorld()->addSound($this->position, new ComposterFillSound());
            }
        }
        $this->position->getWorld()->setBlock($this->position, $this);
        return true;
    }

    public function getComposterFillLevel() : int{
        return $this->composter_fill_level;
    }

    public function getFlameEncouragement() : int{
        return 5;
    }

    public function getFlammability() : int{
        return 20;
    }

    public function getFuelTime() : int{
        return 50;
    }
}