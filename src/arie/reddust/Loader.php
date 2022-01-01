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
use pocketmine\item\ToolTier;
use pocketmine\plugin\PluginBase;

use arie\reddust\block\Composter;
use arie\reddust\block\DaylightSensor;
use arie\reddust\block\Dispenser;
use arie\reddust\block\Dropper;
use arie\reddust\block\JukeBox;
use arie\reddust\block\Lever;
use arie\reddust\block\Piston;
use arie\reddust\block\Observer;

use arie\reddust\block\Hopper;
use arie\reddust\block\RedStoneLamp;
use arie\reddust\block\RedStoneWire;
use arie\reddust\block\RedstoneComparator;
use arie\reddust\block\RedstoneRepeater;
use arie\reddust\block\RedstoneTorch;
use arie\reddust\block\tile\Hopper as HopperTile;
use arie\reddust\block\utils\ComposterUtils;

final class Loader extends PluginBase {
    /** @var Loader */
    private static self $instance;

    /** @var ComposterUtils  */
    private ComposterUtils $composterUtils;

    protected function onLoad() : void {
        self::$instance = $this;
        $this->registerBlocks();
    }

    private function registerBlocks() : void{
        $hopper = VanillaBlocks::HOPPER();
        TileFactory::getInstance()->register(HopperTile::class, ["Hopper", "minecraft:hopper"]);
        BlockFactory::getInstance()->register(new Hopper(new BlockIdentifier($hopper->getIdInfo()->getBlockId(), $hopper->getIdInfo()->getVariant(), $hopper->getIdInfo()->getItemId(), HopperTile::class), $hopper->getName(), $hopper->getBreakInfo()), true);
        //BlockFactory::getInstance()->register(new Composter(new BlockIdentifier(BlockLegacyIds::COMPOSTER, 0), "Composter", new BlockBreakInfo(0.6, BlockToolType::AXE, 0, 0)));
    }

    public function getComposterUtils() : ?ComposterUtils{
        return $this->composterUtils;
    }

    protected function onEnable() : void {
        $this->getLogger()->info("Nothing here!");
        $this->composterUtils = new ComposterUtils($this);
    }

    public static function getInstance() : self {
        return self::$instance;
    }
}