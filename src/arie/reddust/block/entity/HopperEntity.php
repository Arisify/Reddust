<?php
declare(strict_types=1);

namespace arie\reddust\block\entity;

use pocketmine\block\tile\Hopper;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

class HopperEntity extends Hopper {
    public const TAG_TRANSFER_COOLDOWN = "TransferCooldown";
    private int $transfer_cooldown = 0;
    
    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);
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
}