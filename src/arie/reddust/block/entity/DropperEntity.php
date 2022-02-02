<?php

namespace arie\reddust\block\entity;

use pocketmine\block\tile\Container;
use pocketmine\block\tile\ContainerTrait;
use pocketmine\block\tile\Nameable;
use pocketmine\block\tile\NameableTrait;
use pocketmine\block\tile\Spawnable;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\world\World;

use arie\reddust\block\inventory\DropperInventory;

class DropperEntity extends Spawnable implements Container, Nameable{
    use ContainerTrait;
    use NameableTrait;

    /** @var DropperInventory */
    private DropperInventory $inventory;

    public function __construct(World $world, Vector3 $pos){
        parent::__construct($world, $pos);
        $this->inventory = new DropperInventory($this->position);
    }

    public function readSaveData(CompoundTag $nbt) : void{
        $this->loadItems($nbt);
        $this->loadName($nbt);
    }

    protected function writeSaveData(CompoundTag $nbt) : void{
        $this->saveItems($nbt);
        $this->saveName($nbt);
    }

    public function getDefaultName() : string{
        return "Dropper";
    }

    /**
     * @return DropperInventory
     */
    public function getInventory(){
        return $this->inventory;
    }

    /**
     * @return DropperInventory
     */
    public function getRealInventory(){
        return $this->inventory;
    }

    protected function addAdditionalSpawnData(CompoundTag $nbt) : void{
        //NOOP
    }
}
