<?php
declare(strict_types=1);

namespace arie\reddust\block\utils;

use pocketmine\world\Position;

trait ComparatorContainerOutputTrait{
    abstract protected function getPosition() : Position;

    public function getComparatorOutput(): int{
        $inventory = $this->getPosition()->getWorld()->getTile($this->getPosition())?->getInventory();
        if ($inventory === null) return 0;
        $signal = 0;
        for ($slot = 0; $slot < $inventory->getSize(); ++$slot) {
            $item = $inventory->getItem($slot);
            $count = $item->getCount();
            $maxStack = $item->getMaxStackSize();
            $signal += match($maxStack) {
                1 => 3,
                default => $count/$maxStack
            };
        }
        return (int) floor($signal);
    }
}