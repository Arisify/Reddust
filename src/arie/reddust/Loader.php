<?php
declare(strict_types=1);

namespace arie\reddust;

use arie\reddust\block\Dispenser;
use arie\reddust\block\entity\DispenserEntity;
use arie\reddust\listener\InventoryListener;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\TileFactory;
use pocketmine\item\ItemIds;
use pocketmine\plugin\PluginBase;

use arie\reddust\block\Dropper;
use arie\reddust\block\Hopper;
use arie\reddust\block\entity\DropperEntity;
use arie\reddust\block\entity\HopperEntity;

final class Loader extends PluginBase{
	/** @var Loader */
	private static self $instance;

	protected function onLoad() : void{
		self::$instance = $this;
		$this->registerBlocks();
	}

	protected function registerBlocks() : void{
		$hopper = VanillaBlocks::HOPPER();
		TileFactory::getInstance()->register(HopperEntity::class, ["Hopper", "minecraft:hopper"]);
		TileFactory::getInstance()->register(DispenserEntity::class, ["Dispenser", "minecraft:dispenser"]);
		TileFactory::getInstance()->register(DropperEntity::class, ["Dropper", "minecraft:dropper"]);
		BlockFactory::getInstance()->register(new Hopper(new BlockIdentifier($hopper->getIdInfo()->getBlockId(), $hopper->getIdInfo()->getVariant(), $hopper->getIdInfo()->getItemId(), HopperEntity::class), $hopper->getName(), $hopper->getBreakInfo()), true);
		BlockFactory::getInstance()->register(new Dropper(new BlockIdentifier(BlockLegacyIds::DROPPER, 0, ItemIds::DROPPER, DropperEntity::class), "Dropper", new BlockBreakInfo(3.5, BlockToolType::PICKAXE)));
		BlockFactory::getInstance()->register(new Dispenser(new BlockIdentifier(BlockLegacyIds::DISPENSER, 0, ItemIds::DISPENSER, DispenserEntity::class), "Dispenser", new BlockBreakInfo(3.5, BlockToolType::PICKAXE)));
	}

	protected function onEnable() : void{
		$this->getLogger()->info("Nothing here!");
		$this->getServer()->getPluginManager()->registerEvents(new InventoryListener(), $this);
	}

	public static function getInstance() : self{
		return self::$instance;
	}
}
