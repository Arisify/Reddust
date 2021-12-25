<?php
declare(strict_types=1);

namespace arie\reddust\event\composter;

use pocketmine\block\Block;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;
use pocketmine\player\Player;

class ComposterEvent extends BlockEvent implements Cancellable{
    use CancellableTrait;

    public function __construct(
        private Block | Player $origin,
        Block $block
    ){
        parent::__construct($block);
    }

    /**
     * Return the player or block that trigger the composter
     */
    public function getOrigin() : Block | Player | null{
        return $this->origin;
    }
}