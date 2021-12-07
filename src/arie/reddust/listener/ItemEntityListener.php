<?php
declare(strict_types=1);
namespace arie\reddust\listener;

use pocketmine\block\tile\Hopper as PmHopperTile;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\Listener;

use arie\reddust\Loader;

use muqsit\asynciterator\AsyncIterator;
use muqsit\asynciterator\handler\AsyncForeachHandler;
use muqsit\asynciterator\handler\AsyncForeachResult;

use ArrayIterator;
use LogicException;

final class ItemEntityListener implements Listener{
    /** @var AsyncIterator */
    private AsyncIterator $async_iterator;

    /** @var AsyncForeachHandler<int, ItemEntity>|null */
    private ?AsyncForeachHandler $ticker;

    private array $entities = [];

    public function __construct(
        private Loader $plugin,
    ) {

        $this->async_iterator = new AsyncIterator($plugin->getScheduler());
        $this->plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
        foreach ($this->plugin->getServer()->getWorldManager()->getWorlds() as $world) {
            foreach ($world->getEntities() as $entity) {
                if ($entity instanceof ItemEntity) {
                    $this->registerItemEntity($entity);
                }
            }
        }
    }

    public function getEntities() : array {
        return $this->entities;
    }

    public function onItemEntityMove(ItemEntity $entity) {
        $position = $entity->getPosition();
        for ($i = 0; $i >= -1; --$i) {
            $tile = $position->getWorld()->getTile($position->add(0, $i, 0));
            if ($tile instanceof PmHopperTile && $position->y - $tile->getPosition()->y < 0.75) {
                $item = $entity->getItem();
                if (!$item->isNull()) {
                    $residue_count = 0;
                    foreach ($tile->getInventory()->addItem($item) as $residue) {
                        $residue_count += $residue->getCount();
                    }
                    if ($residue_count === 0) {
                        $entity->flagForDespawn();
                    } else {
                        $item->setCount($residue_count);
                    }
                    break;
                }
            }
        }
    }

    public function registerItemEntity(ItemEntity $entity) : void{
        if (!$entity->isClosed() && !$entity->isFlaggedForDespawn()) {
            $this->entities[$entity->getId()] = $entity;
            if ($this->ticker === null) $this->tick();
        }
    }

    public function deregisterItemEntity(ItemEntity $entity) {
        if (isset($this->entities[$id = $entity->getId()])) {
            unset($this->entities[$id]);
            if ($this->ticker !== null && count($this->entities) === 0) {
                $this->ticker->cancel();
                $this->ticker = null;
            }
        }
    }

    public function onItemSpawn(ItemSpawnEvent $event) {
        $entity = $event->getEntity();
        if ($entity instanceof ItemEntity) {
            $this->registerItemEntity($entity);
        }
    }

    public function onItemDespawn(ItemDespawnEvent $event) {
        $entity = $event->getEntity();
        if ($entity instanceof ItemEntity)
            $this->deregisterItemEntity($entity);
    }

    public function isTicking() : bool{
        return $this->ticker !== null;
    }

    private function tick() : bool{
        if($this->ticker !== null){
            throw new LogicException("Tried scheduling multiple item entity tickers");
        }
        $this->ticker = $this->async_iterator->forEach(new ArrayIterator($this->entities), 1, 4)->as(static function(int $id, ItemEntity $entity) : AsyncForeachResult{
            if(!$entity->isClosed() && !$entity->isFlaggedForDespawn()){
                $this->onItemEntityMove($entity);
            }
            return AsyncForeachResult::CONTINUE();
        })->onCompletion(function() : void{
            $this->ticker = null;
            $this->tick();
        });
        return true;
    }
}