<?php
declare(strict_types=1);

namespace arie\reddust\block\tile;

use pocketmine\block\tile\Spawnable;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;

class Composter extends Spawnable{
    public const TAG_COMPOSTER_FILL_LEVEL = "composter_fill_level";
    /** @var int */
    private int $composter_fill_level = 0;

    public function readSaveData(CompoundTag $nbt) : void{
        if (($tag = $nbt->getTag(self::TAG_COMPOSTER_FILL_LEVEL)) instanceof ByteTag){
            assert($tag->getValue() >= 0 && $tag->getValue() <= 8);
            $this->composter_fill_level = $tag->getValue();
        }else{
            $this->composter_fill_level = 0;
        }
    }

    public function setComposterFillLevel(int $layer) : void{
        assert($layer >= 0 && $layer <= 8);
        $this->composter_fill_level = $layer;
    }

    public function getComposterFillLevel() : int{
        return $this->composter_fill_level;
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $nbt->setByte(self::TAG_COMPOSTER_FILL_LEVEL, $this->composter_fill_level);
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        $nbt->setByte(self::TAG_COMPOSTER_FILL_LEVEL, $this->composter_fill_level);
    }
}
