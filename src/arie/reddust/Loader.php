<?php
declare(strict_types=1);

namespace arie\reddust;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\VanillaBlocks;
use pocketmine\block\tile\TileFactory;
use pocketmine\plugin\PluginBase;

use arie\reddust\block\entity\HopperEntity;
use arie\reddust\block\Hopper;

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
