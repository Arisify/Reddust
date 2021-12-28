<?php
declare(strict_types=1);
namespace arie\reddust\circuit;

use pocketmine\world\Position;

class CircuitSceneGraph{
    public function update() {
        return;
    }

    public function getComponents_FastLookupByChunkPos() {
        return [];
    }

    public function getBaseComponent(Position $position) : ?Component{
        return null;
    }
}