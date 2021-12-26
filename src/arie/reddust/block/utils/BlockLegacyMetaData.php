<?php
declare(strict_types=1);

namespace arie\reddust\block\utils;

use pocketmine\block\BlockLegacyMetadata as PmBlockMData;

class BlockLegacyMetaData extends PmBlockMData{
    public const OBSERVER_FLAG_LIT = 0x08;
}