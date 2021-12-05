<?php
declare(strict_types=1);

use pocketmine\block\Hopper as PmHopper;
use pocketmine\block\inventory\HopperInventory;
use pocketmine\block\tile\Hopper as PmHopperTile;
use pocketmine\block\tile\Furnace;

use pocketmine\block\tile\Container;


use pocketmine\block\inventory\FurnaceInventory;
use pocketmine\crafting\FurnaceRecipeManager;
use pocketmine\crafting\FurnaceType;

use pocketmine\block\tile\Tile;
use pocketmine\math\Facing;

use pocketmine\inventory\Inventory;
use pocketmine\Server;
class Hopper extends PmHopper {
    /** @var int */
    public readonly int $transfer_cooldown = 0;

    /** @var FurnaceRecipeManager */
    private FurnaceRecipeManager $furnace_recipe_manager;

    public function __construct(FurnaceType $type){
        $this->furnace_recipe_manager = Server::getInstance()->getCraftingManager()->getFurnaceRecipeManager($type);
    }

    public function getInventory() : ?HopperInventory{
        $tile = $this->position->getWorld()->getTile($this->position);
        return $tile instanceof PmHopperTile ? $tile->getInventory() : null;
    }

    public function getContainerAbove() : ?Container{
        $above = $this->position->getWorld()->getTile($this->getPosition()->getSide(Facing::UP));
        return $above instanceof Container ? $above : null;
    }

    public function getContainerFacing() : ?Container{
        $facing = $this->position->getWorld()->getTile($this->position->getSide($this->facing));
        return $facing instanceof Container ? $facing->getInventory() : null;
    }

    protected function pull() : bool{
        return true;
    }

    protected function push() : bool {
        $facing = $this->getContainerFacing();
        if ($facing instanceof Furnace) {
            $face = $this->getFacing();
            $facing_inventory = $facing->getInventory();
            //assert($face != Facing::UP);
            if ($face == Facing::DOWN) {
                $smelting = $facing_inventory->getSmelting();
                if ($smelting === null ? $this->furnace_recipe_manager->match($item) : $item->equals($smelting)) {
                    $smelting = (new $item)->setCount(($smelting->getCount() ?? 0) + 1);
                    $facing_inventory->setSmelting($smelting);
                }
            } else {
                $fuel = $facing->getInventory()->getFuel();
                if ($fuel->getCount() < $fuel->getMaxStackSize()) {

                }
            }
        }
        $this->getInventory()->setItem($slot, $item);
        return true;
    }

    public function onScheduledUpdate(): void {
        parent::onScheduledUpdate();
        $hopper_inventory = $this->getInventory();
        $this->transfer_cooldown--;
        if ($this->transfer_cooldown <= 0) {
            /**if (!empty($hopper_inventory->getContents())) {
            } else {
            }*/
        }
    }
}