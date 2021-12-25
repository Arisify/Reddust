<?php
declare(strict_types=1);

namespace arie\reddust\event\composter;

use pocketmine\block\Block;
use pocketmine\player\Player;

class ComposterReadyEvent extends ComposterEvent{
    public function __construct(
        Player | Block $origin,
        Block $block
    ){
        parent::__construct($origin, $block);
    }
}


