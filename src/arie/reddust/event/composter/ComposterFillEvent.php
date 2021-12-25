<?php
declare(strict_types=1);

namespace arie\reddust\event\composter;

use pocketmine\block\Block;
use pocketmine\player\Player;

class ComposterFillEvent extends ComposterEvent{
    public function __construct(
        Player | Block $origin,
        Block $block,
        private bool $success = false
    ){
        parent::__construct($origin, $block);
    }

    public function isSuccess() : bool{
        return $this->success;
    }

    public function getComposterFillLevel() : ?int{
        return $this->getBlock()->getComposterFillLevel();
    }
}


