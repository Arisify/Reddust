<?php
declare(strict_types=1);

namespace arie\reddust\block;

use muqsit\pmhopper\item\ItemEntityMovementNotifier;
use pocketmine\block\VanillaBlocks;
use pocketmine\item\BeetrootSeeds;
use pocketmine\item\DriedKelp;
use pocketmine\item\Item;
use pocketmine\item\ItemBlock;
use pocketmine\item\ItemIdentifier;
use pocketmine\item\ItemIds;
use pocketmine\item\MelonSeeds;
use pocketmine\item\PumpkinSeeds;

class ComposerUtils {

    protected static array $list = [];

    public function __construct(
        private $plugin
    ) {
        self::register(new BeetrootSeeds(new ItemIdentifier(ItemIds::BEETROOT_SEEDS, 0), "Beetroot Seeds"));
        self::register(new DriedKelp(new ItemIdentifier(ItemIds::DRIED_KELP, 0), "Dried Kelp"));
        //Todo: add glow berry (?:?)
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::TALL_GRASS, 0), VanillaBlocks::TALL_GRASS()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::TALL_GRASS, 1), VanillaBlocks::TALL_GRASS()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::TALL_GRASS, 2), VanillaBlocks::TALL_GRASS()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::TALL_GRASS, 3), VanillaBlocks::TALL_GRASS()));
        //ItemIds::DOUBLE_PLANT;
        //VanillaBlocks::DOUBLE_TALLGRASS();
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::GRASS, 0), VanillaBlocks::GRASS()));
        //Todo: add hanging roots (574:0)
        //Todo: add kelp (335:0)

        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 0), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 1), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 2), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 3), VanillaBlocks::JUNGLE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 4), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 5), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 6), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 7), VanillaBlocks::JUNGLE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 8), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 9), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 10), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 11), VanillaBlocks::JUNGLE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 12), VanillaBlocks::OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 13), VanillaBlocks::SPRUCE_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 14), VanillaBlocks::BIRCH_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES, 15), VanillaBlocks::JUNGLE_LEAVES()));


        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 0), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 1), VanillaBlocks::DARK_OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 4), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 5), VanillaBlocks::DARK_OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 8), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 9), VanillaBlocks::DARK_OAK_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 12), VanillaBlocks::ACACIA_LEAVES()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::LEAVES2, 13), VanillaBlocks::DARK_OAK_LEAVES()));

        self::register(new MelonSeeds(new ItemIdentifier(ItemIds::MELON_SEEDS, 0), "Melon Seeds"));
        //Todo: add moss carpet (590:0)

        self::register(new PumpkinSeeds(new ItemIdentifier(ItemIds::PUMPKIN_SEEDS, 0), "Pumkin Seeds"));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 0), VanillaBlocks::OAK_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 1), VanillaBlocks::SPRUCE_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 2), VanillaBlocks::BIRCH_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 3), VanillaBlocks::JUNGLE_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 4), VanillaBlocks::ACACIA_SAPLING()));
        self::register(new ItemBlock(new ItemIdentifier(ItemIds::SAPLING, 5), VanillaBlocks::DARK_OAK_SAPLING()));

        //Todo: add sea grass
    }

    public static function register(Item $item, $percentage = 30) : bool{
        if (isset(self::$list[$item->getId() . ($item->getMeta() !== 0) ? ":" . $item->getMeta() : ""])) return false;
        self::$list[$item->getId() . ($item->getMeta() !== 0) ? ":" . $item->getMeta() : ""] = $percentage;
        return true;
    }

    public static function isCompostable(Item $item) : bool{
        return isset(self::$list[$item->getId() . ($item->getMeta() !== 0) ? ":" . $item->getMeta() : ""]);
    }

    public static function getPercentage(Item $item) {
        return self::$list[$item->getId() . ($item->getMeta() !== 0) ? ":" . $item->getMeta() : ""] ?? 0;
    }
}