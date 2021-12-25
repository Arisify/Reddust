<?php
declare(strict_types=1);

namespace arie\reddust\event\composter;

use pocketmine\block\Block;
use pocketmine\player\Player;

class ComposterEmptyEvent extends ComposterEvent{
    public function __construct(
        Player | Block $origin,
        Block $block,
        protected array $drops
    ){
        parent::__construct($origin, $block);
    }

    public function getDrops() : ?array{
        return $this->drops;
    }

    public function setDrops(?array $drops = null) {
        $this->drops = $drops ?? [];
    }
}


