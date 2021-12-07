<?php
declare(strict_types=1);

namespace arie\reddust\item;

use pocketmine\entity\object\ItemEntity;
use pocketmine\world\Position;

final class ItemEntityMovementNotifier{

    public function __construct(
        private ItemEntity $entity,
        private ItemEntityListener $listener,
    ) {
        $this->check();
    }

    public function check() {
        $this->listener->onItemEntityMove($this->entity);
    }

    public function update() : void{
        if (!$this->entity->isClosed() && !$this->entity->isFlaggedForDespawn()) {
            $this->check();
        }
    }
}