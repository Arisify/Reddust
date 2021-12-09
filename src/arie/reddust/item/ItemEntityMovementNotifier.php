<?php
declare(strict_types=1);

namespace arie\reddust\item;

use pocketmine\entity\object\ItemEntity;

final class ItemEntityMovementNotifier{

    public function __construct(
        private ItemEntity $entity,
        private ItemEntityListener $listener,
    ) {
        $this->check();
    }

    public function check() : void{
        $this->listener->onItemEntityMove($this->entity);
    }

    public function update() : void{
        if (!$this->entity->isClosed() && !$this->entity->isFlaggedForDespawn()) {
            $this->check();
        }
    }
}