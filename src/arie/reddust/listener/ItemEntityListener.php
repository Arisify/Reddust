<?php
declare(strict_types=1);

namespace arie\reddust\listener;

use pocketmine\block\tile\Hopper as PmHopperTile;
use pocketmine\entity\object\ItemEntity;
use pocketmine\event\entity\ItemDespawnEvent;
use pocketmine\event\entity\ItemSpawnEvent;

use pocketmine\event\Listener;
//use pocketmine\world\World;

use arie\reddust\Loader;
use pocketmine\world\Position;

//use arie\reddust\block\Hopper;

final class ItemEntityListener implements Listener{
    private $entities = [];

    public function __construct(
        private Loader $plugin,
    ) {
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
            $this->entities[$entity->getId()] = $entity->getId();
        }
    }

    public function deregisterItemEntity(ItemEntity $entity) {
        if (isset($this->entities[$id = $entity->getId()])) {
            unset($this->entities[$id]);
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
}