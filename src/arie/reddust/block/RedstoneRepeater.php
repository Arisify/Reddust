<?php
declare(strict_types=1);
namespace arie\reddust\block;

use pocketmine\block\RedstoneRepeater as PmRedstoneRepeater;

class RedstoneRepeater extends PmRedstoneRepeater {

    public function canBeFlowedInto() : bool{
        return false;
    }

}