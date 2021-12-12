<?php
declare(strict_types=1);

namespace arie\reddust;

use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\tile\TileFactory;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\ToolTier;
use pocketmine\plugin\PluginBase;

use arie\reddust\block\Hopper;
use arie\reddust\block\Composter;
use arie\reddust\block\tile\Hopper as HopperTile;
use arie\reddust\block\tile\Composter as ComposterTile;
use arie\reddust\block\utils\ComposterUtils;
use arie\reddust\item\ItemEntityListener;

final class Loader extends PluginBase {
    /** @var Loader */
    private static self $instance;


    /** @var ItemEntityListener */
    private ItemEntityListener $item_entity_listener;

    /** @var ComposterUtils  */
    private ComposterUtils $composterUtils;

    protected function onLoad() : void {
        self::$instance = $this;
        $hopper = VanillaBlocks::HOPPER();

        TileFactory::getInstance()->register(HopperTile::class, ["Hopper", "minecraft:hopper"]);
        TileFactory::getInstance()->register(ComposterTile::class, ["Composter", "minecraft:composter"]);

        //BlockFactory::getInstance()->register(new Hopper($hopper->getIdInfo(), $hopper->getName(), $hopper->getBreakInfo()), true);
        BlockFactory::getInstance()->register(new Hopper(
            new BlockIdentifier($hopper->getIdInfo()->getBlockId(), $hopper->getIdInfo()->getVariant(), $hopper->getIdInfo()->getItemId(), HopperTile::class),
            $hopper->getName(),
            $hopper->getBreakInfo()),
            true
        );

        BlockFactory::getInstance()->register(new Composter(new BlockIdentifier(BlockLegacyIds::COMPOSTER, 0, 468, ComposterTile::class), "Composter", new BlockBreakInfo(0.6, BlockToolType::AXE, ToolTier::WOOD()->getHarvestLevel(), 0.6)));
    }

    public static function getInstance() : self {
        return self::$instance;
    }

    protected function onEnable() : void {
        $this->getLogger()->info("Nothing here to see you silly!");
        //$this->item_entity_listener = new ItemEntityListener($this);
        $this->composerUtils = new ComposterUtils($this);
    }
}