<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Transparent;
use pocketmine\item\Item;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

use arie\reddust\block\utils\ComposerUtils;

class Composer extends Transparent {

    /** @var int */
    protected int $composter_fill_level = 0;

    protected function writeStateToMeta() : int{
        return $this->composter_fill_level;
    }

    public function readStateFromData(int $id, int $stateMeta) : void{
        $this->composter_fill_level = $stateMeta;
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
            $item = $player->getInventory()->getItemInHand()->pop();
            $this->compost($item);
            return true;
        }
        return false;
    }

    /**
     * @throws \Exception
     */
    public function compost(Item $item) : bool{
        if (!ComposerUtils::isCompostable($item)) return false;
        $percent = ComposerUtils::getPercentage($item);
        if ($this->composter_fill_level < 8 && random_int(1, (int) (100/$percent)) === 1) {
            $this->composter_fill_level++;
            return true;
        }
        return false;
    }

    public function addComposterFillLayer(int $layer = 1) : bool{
        if ($this->composter_fill_level + $layer > 8) return false;
        $this->composter_fill_level += $layer;
        return true;
    }

    public function setComposterFillLevel(int $layer) {
        assert($layer > 8 or $layer < 0, "Invalid composter fill level has been set, the valid level should be between 0 and 8.");
        $this->composter_fill_level = $layer;
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