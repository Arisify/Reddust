<?php
declare(strict_types=1);

namespace arie\reddust\block\tile;

use pocketmine\block\tile\Hopper as PmHopperTile;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class Hopper extends PmHopperTile {
    public const TAG_TRANSFER_COOLDOWN = "TransferCooldown";
    private int $transfer_cooldown = 0;
    
    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
        $this->collectBoxes =  [
            new AxisAlignedBB(
                $this->position->getFloorX() + 3/16,
                $this->position->getFloorY() + 10/16,
                $this->position->getFloorZ() +3/16,
                $this->position->getFloorX()+ 13/16,
                $this->position->getFloorY() + 1,
                $this->position->getFloorZ() + 13/16,
            ),
            new AxisAlignedBB(
                $this->position->getFloorX(),
                $this->position->getFloorY() + 1,
                $this->position->getFloorZ(),
                $this->position->getFloorX()+ 1,
                $this->position->getFloorY() + 1.75,
                $this->position->getFloorZ() + 1,
            )
        ];
    }

    public function readSaveData(CompoundTag $nbt) : void{
        parent::readSaveData($nbt);
        $this->transfer_cooldown = $nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 0);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        parent::writeSaveData($nbt);
        $nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->transfer_cooldown);
    }

    public function getTransferCooldown() : int{
        return $this->transfer_cooldown;
    }

    public function setTransferCooldown($cooldown = 0) {
        $this->transfer_cooldown = 0;
    }

    public function getCollectBoxes() : array{
        return $this->collectBoxes;
    }
}