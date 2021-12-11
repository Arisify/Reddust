<?php
declare(strict_types=1);

namespace arie\reddust\block\tile;

use pocketmine\block\tile\Hopper as PmHopperTile;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Vector3;
use pocketmine\world\World;

class Hopper extends PmHopperTile {

    /** @var AxisAlignedBB[] */
    protected array $collectCollisionBoxes = [];
    
    public function __construct(World $world, Vector3 $pos) {
        parent::__construct($world, $pos);

        $this->collectCollisionBoxes =  [
            new AxisAlignedBB(
                $this->position->getX(),
                $this->position->getY() + 1,
                $this->position->getZ(),
                $this->position->getX()+ 1,
                $this->position->getY() + 1.75,
                $this->position->getZ() + 1,
            ),
            new AxisAlignedBB(
                $this->position->getX() + 3/16,
                $this->position->getY() + 10/16,
                $this->position->getZ() +3/16,
                $this->position->getX()+ 13/16,
                $this->position->getY() + 1,
                $this->position->getZ() + 13/16,
            )
        ];
    }

    public function getCollectCollisionBoxes(): array{
        return $this->collectCollisionBoxes;
    }
}