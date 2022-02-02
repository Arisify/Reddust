<?php
declare(strict_types=1);

namespace arie\reddust;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\TileFactory;
use pocketmine\crafting\ShapedRecipe;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\VanillaItems;
use pocketmine\plugin\PluginBase;

use arie\reddust\block\DaylightSensor;
use arie\reddust\block\Dispenser;
use arie\reddust\block\Dropper;
use arie\reddust\block\entity\HopperEntity;
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
use arie\reddust\circuit\CircuitSystem;
use pocketmine\Server;

final class Loader extends PluginBase {
    /** @var Loader */
    private static self $instance;

    protected function onLoad() : void {
        self::$instance = $this;
        $hopper = VanillaBlocks::HOPPER();
        TileFactory::getInstance()->register(HopperEntity::class, ["Hopper", "minecraft:hopper"]);
        BlockFactory::getInstance()->register(new Hopper(new BlockIdentifier($hopper->getIdInfo()->getBlockId(), $hopper->getIdInfo()->getVariant(), $hopper->getIdInfo()->getItemId(), HopperEntity::class), $hopper->getName(), $hopper->getBreakInfo()), true);

    }

    protected function onEnable() : void {
        $this->getLogger()->info("Nothing here!");
    }

    public static function getInstance() : self {
        return self::$instance;
    }
}