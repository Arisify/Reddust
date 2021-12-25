<?php
declare(strict_types=1);

namespace arie\reddust\event\hopper;

use pocketmine\block\Block;
use pocketmine\event\block\BlockEvent;
use pocketmine\event\Cancellable;
use pocketmine\event\CancellableTrait;

class HopperEvent extends BlockEvent implements Cancellable{
    use CancellableTrait;

    public function __construct(
        Block $block
    ){
        parent::__construct($block);
    }
}