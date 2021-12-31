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

       $this->getBlock();

        $this->collectCollisionBoxes =  [
            new AxisAlignedBB(
                $this->position->getX(),
                $this->position->getY() + 1,
                $this->position->getZ(),
                $this->position->getX()+ 1,
                $this->position->getY() + 1.75,
                $this->position->getZ() + 1,
            ),
            new AxisAlignedBB(
                $this->position->getX() + 3/16,
                $this->position->getY() + 10/16,
                $this->position->getZ() +3/16,
                $this->position->getX()+ 13/16,
                $this->position->getY() + 1,
                $this->position->getZ() + 13/16,
            )
        ];
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->loadItems($nbt);
        $this->loadName($nbt);

        $this->transfer_cooldown = $nbt->getInt(self::TAG_TRANSFER_COOLDOWN, 0);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $this->saveItems($nbt);
        $this->saveName($nbt);

        $nbt->setInt(self::TAG_TRANSFER_COOLDOWN, $this->getTransferCooldown());
    }

    public function getCollectCollisionBoxes(): array{
        return $this->collectCollisionBoxes;
    }

    public function getTransferCooldown() : int{
        return ($this->transfer_cooldown > 8 || $this->transfer_cooldown < 0) ? 0 : $this->transfer_cooldown;
    }

    public function setTransferCooldown(int $cooldown = 0) : void{
        assert($cooldown >= 0 && $cooldown < 9);
        $this->transfer_cooldown = $cooldown;
    }
}