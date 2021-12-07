<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Block;
use pocketmine\block\RedstoneTorch as PmRedstoneTorch;
//use pocketmine\block\utils\PoweredByRedstoneTrait;
use pocketmine\item\Item;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\BlockTransaction;

class RedstoneTorch extends PmRedstoneTorch {
    //use PoweredByRedstoneTrait;

    public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null): bool {

        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }
}