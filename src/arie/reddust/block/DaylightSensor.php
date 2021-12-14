<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\DaylightSensor as PmDaylightSensor;

class DaylightSensor extends PmDaylightSensor{
    public function canBeFlowedInto() : bool{
        return false;
    }
}