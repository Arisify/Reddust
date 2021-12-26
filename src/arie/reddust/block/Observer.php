<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Block;
use pocketmine\block\Opaque;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\item\Item;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\player\Player;

use arie\reddust\block\utils\BlockLegacyMetaData;
use pocketmine\world\BlockTransaction;

class Observer extends Opaque{
    protected bool $lit = false

	private int $facing = Facing::NORTH;

	public function readStateFromData(int $id, int $stateMeta) : void{
        $facing = BlockDataSerializer::readFacing($stateMeta & 0x07);
        $this->facing = $facing;
        $this->lit = ($stateMeta & BlockLegacyMetadata::OBSERVER_FLAG_LIT) !== 0;
    }

	protected function writeStateToMeta() : int{
        return BlockDataSerializer::writeFacing($this->facing) | ($this->lit ? BlockLegacyMetadata::OBSERVER_FLAG_LIT : 0);
    }

	public function getStateBitmask() : int{
        return 0b1111;
    }

	public function getFacing() : int{ return $this->facing; }

	/** @return $this */
	public function setFacing(int $facing) : self{
        $this->facing = $facing;
        return $this;
    }

    /**
     * @return $this
     */
    public function setLit(bool $lit = true) : self{
        $this->lit = $lit;
        return $this;
    }

	public function place(BlockTransaction $tx, Item $item, Block $blockReplace, Block $blockClicked, int $face, Vector3 $clickVector, ?Player $player = null) : bool{
        $this->facing = $face === Facing::DOWN ? Facing::DOWN : Facing::opposite($face);

        return parent::place($tx, $item, $blockReplace, $blockClicked, $face, $clickVector, $player);
    }
}
