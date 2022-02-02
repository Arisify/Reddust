<?php
declare(strict_types=1);
namespace arie\reddust\circuit;

use pocketmine\world\Position;

class CircuitSceneGraph{
    private array $struct = [];
    public function update(BlockSource $source) {
        return;
    }

    public function remove(Position $position, Component $component) {
        unset($this->struct[$position->__toString()]);
    }

    public function getComponents_FastLookupByChunkPos() {
        return [];
    }

    public function getBaseComponent(Position $position) : ?Component{
        return null;
    }
}