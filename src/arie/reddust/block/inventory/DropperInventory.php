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

    public function getRandomSlot() : int{ //Worst (Lost every tests :'u) - 0p
        $slots = $this->getContents();
        return $slots ? array_rand($slots) : -1;
    }

    public function getRandomSlot1() : int{ // Good for non-empty array - 5p
        $slots = [];
        for ($slot = 0; $slot < $this->getSize(); ++$slot) {
            if (!$this->slots[$slot] === null) {
                $slots[] = $slot;
            }
        }
        $count = count($slots);
        return $count >=1 ? $slots[mt_rand(0, $count - 1)] : -1;
    }

    public function getRandomSlot2() : int{ //Same as type-3 but op for array has items at the first or last slot - 10p
        $slots = [];
        foreach ($this->slots as $i => $slot) {
            if ($slot !== null) {
                $slots[$i] = $slots;
            }
        }
        $count = count($slots);
        return $count >= 1 ? mt_rand(0, $count - 1) : -1;
    }

    public function getRandomSlot3() : int{ //The best, good for all but usually lost to its cousin, type-2 (maths?) - 15p
        $slots = [];
        foreach ($this->slots as $i => $slot) {
            if ($slot !== null) {
                $slots[$i] = $slots;
            }
        }
        return $slots ? mt_rand(0, count($slots) - 1) : -1;
    }

    /* Test results: (Each had 3 times): the results are the average value of 10000 runtime per each type
     * Empty:             2, 2, 3
     * 1st slot:          3, 2, 2
     * Last slot:         2, 3, 2
     * Mid slot:          3, 2, 3
     * Fill all:          1, 3, 1
     * Cross:             3, 3, 2
     * Offset cross:      3, 2, 3 (They are so close...)
     * 2nd slot:          3, 3, 3
     * Offset last slot:  3, 2, 2
     * Fill but not 1st:  1, 1, 1 (Type 1 is superior xd)
     * Fill but not last: 3, 1, 3
     * Total:
     *          Type 0: 0
     *          Type 1: 5
     *          Type 2: 10
     *          Type 3: 15
     * Ratio? IDK but type 3 won the battle :3
     */
}
