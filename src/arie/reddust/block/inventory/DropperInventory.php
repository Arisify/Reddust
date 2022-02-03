<?php
declare(strict_types=1);

namespace arie\reddust\block\inventory;

use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\BlockInventoryTrait;
use pocketmine\inventory\SimpleInventory;
use pocketmine\network\mcpe\protocol\types\inventory\WindowTypes;
use pocketmine\world\Position;

class DropperInventory extends SimpleInventory implements BlockInventory, IWindowType{
    use BlockInventoryTrait;

    public function __construct(Position $holder, int $size = 9){
        $this->holder = $holder;
        parent::__construct($size);
    }

    public function getWindowType(): int{
        return WindowTypes::DROPPER;
    }

    public function getRandomSlot() : int{ //3rd, sometimes 2nd
        $slots = $this->getContents();
        return $slots ? array_rand($slots) : -1;
    }

    public function getRandomSlot1() : int{ //2nd
        $slots = [];
        for ($slot = 0; $slot < $this->getSize(); ++$slot) {
            if (!$this->slots[$slot] === null) {
                $slots[] = $slot;
            }
        }
        $count = count($slots);
        return $count >=1 ? $slots[mt_rand(0, $count - 1)] : -1;
    }

    public function getRandomSlot2() : int{ //x
        $slots = $this->getContents();
        $count = count($slots);
        return $count >=1 ? mt_rand(0, $count - 1) : -1;
    }

    public function getRandomSlot3() : int{ //1st all the time
        $slots = [];
        foreach ($this->slots as $i => $slot) {
            if ($slot !== null) $slots[$i] = $slots;
        }
        return $slots ? mt_rand(0, count($slots)) : -1;
    }
}
