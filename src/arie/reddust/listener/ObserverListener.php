<?php
declare(strict_types=1);

namespace arie\reddust\listener;

use pocketmine\event\block\BlockUpdateEvent;
use pocketmine\event\Listener;
use pocketmine\math\Facing;

use arie\reddust\block\Observer;
use arie\reddust\Loader;

final class ObserverListener implements Listener{
    public function __construct(
        private Loader $plugin
    ) {
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
    }

    public function onBlockUpdate(BlockUpdateEvent $event) {
        $block = $event->getBlock();
        foreach (Facing::ALL as $face) {
            if (($side = $block->getSide($face)) instanceof Observer && Facing::opposite($side->getFacing()) === $face) {
                //Todo ?
            }
        }
    }
}