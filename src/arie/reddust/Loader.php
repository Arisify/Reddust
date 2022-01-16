<?php
declare(strict_types=1);

namespace arie\reddust;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\TileFactory;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;

use arie\reddust\block\Composter;
use arie\reddust\block\DaylightSensor;
use arie\reddust\block\Dispenser;
use arie\reddust\block\Dropper;
use arie\reddust\block\Hopper;
use arie\reddust\block\JukeBox;
use arie\reddust\block\Lever;
use arie\reddust\block\Observer;
use arie\reddust\block\Piston;
use arie\reddust\block\RedStoneLamp;
use arie\reddust\block\RedStoneWire;
use arie\reddust\block\RedstoneComparator;
use arie\reddust\block\RedstoneRepeater;
use arie\reddust\block\RedstoneTorch;
use arie\reddust\block\tile\Hopper as HopperTile;
use arie\reddust\block\utils\ComposterUtils;
use arie\reddust\circuit\CircuitSystem;

final class Loader extends PluginBase {
    /** @var Loader */
    private static self $instance;

    /** @var ComposterUtils  */
    private CircuitSystem $circuitSystem;

    protected function onLoad() : void {
        self::$instance = $this;
        $this->registerBlocks();
    }

    private function registerBlocks() : void{
        $hopper = VanillaBlocks::HOPPER();
        TileFactory::getInstance()->register(HopperTile::class, ["Hopper", "minecraft:hopper"]);
        BlockFactory::getInstance()->register(new Hopper(new BlockIdentifier($hopper->getIdInfo()->getBlockId(), $hopper->getIdInfo()->getVariant(), $hopper->getIdInfo()->getItemId(), HopperTile::class), $hopper->getName(), $hopper->getBreakInfo()), true);
         //2 is my guessed number because wiki sucks! In vanilla, it seems
    }

    public function registerCraftingRecipes() : void{
        $this->getServer()->getCraftingManager()->registerShapedRecipe(new ShapedRecipe(
            [
                'A A',
                'ABA',
                ' A '
            ],
            ['A' => ItemFactory::getInstance()->get(ItemIds::IRON_INGOT), 'B' => VanillaBlocks::CHEST()],
            [BlockFactory::getInstance()->get(ItemIds::HOPPER)]
        ));
    }

    public function getCircuitSystem() : ?CircuitSystem{
        return $this->circuitSystem;
    }

    protected function onEnable() : void {
        $this->getLogger()->info("Nothing here!");
        $this->composterUtils = new ComposterUtils($this);
        $this->circuitSystem = new CircuitSystem($this);
    }

    public static function getInstance() : self {
        return self::$instance;
    }
}