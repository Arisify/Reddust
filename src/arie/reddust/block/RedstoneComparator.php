<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\RedstoneComparator as PmRedstoneComparator;

class RedstoneComparator extends PmRedstoneComparator{
    public function canBeFlowedInto() : bool{
        return false;
    }
}