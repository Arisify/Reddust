<?php
declare(strict_types=1);

namespace arie\reddust\block\utils;

use pocketmine\world\Position;

trait ComparatorContainerOutputTrait{
    abstract protected function getPosition() : Position;

    public function getComparatorOutput(): int{
        $inventory = $this->getPosition()->getWorld()->getTile($this->getPosition())?->getInventory();
        if ($inventory === null) return 0;
        $fullness = 0;
        for ($slot = 0; $slot < $inventory->getSize(); ++$slot) {
            $item = $inventory->getItem($slot);
            $fullness += $item->getCount() / $item->getMaxStackSize();
        }
        return min((int) floor(1 + ($fullness / $inventory->getSize())*14), 15);
    }
}