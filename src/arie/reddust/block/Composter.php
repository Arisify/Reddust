<?php
declare(strict_types=1);

namespace arie\reddust\block;

use pocketmine\block\Block;
use pocketmine\block\BlockBreakInfo;
use pocketmine\block\BlockIdentifier;
use pocketmine\block\BlockLegacyIds;
use pocketmine\block\BlockToolType;
use pocketmine\block\Transparent;
use pocketmine\block\utils\BlockDataSerializer;
use pocketmine\entity\Entity;
use pocketmine\entity\object\ItemEntity;
use pocketmine\entity\projectile\Projectile;
use pocketmine\item\Item;
use pocketmine\item\ItemIdentifier;
use pocketmine\math\AxisAlignedBB;
use pocketmine\math\Facing;
use pocketmine\math\Vector3;
use pocketmine\network\mcpe\protocol\SetActorMotionPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\world\particle\HappyVillagerParticle;

use arie\reddust\block\utils\CompairatorOutputTrait;
use arie\reddust\block\utils\ComposterUtils;
use arie\reddust\event\composter\ComposterEmptyEvent;
use arie\reddust\event\composter\ComposterFillEvent;
use arie\reddust\event\composter\ComposterReadyEvent;
use arie\reddust\world\sound\ComposterEmptySound;
use arie\reddust\world\sound\ComposterFillSound;
use arie\reddust\world\sound\ComposterFillSuccessSound;
use arie\reddust\world\sound\ComposterReadySound;

class Composter extends Transparent {
    use CompairatorOutputTrait;

    /** @var int */
    protected int $composter_fill_level = 0;

    protected function writeStateToMeta() : int{
        return $this->composter_fill_level;
    }

    public function writeStateToItemMeta(): int {
        return $this->composter_fill_level;
    }
    public function readStateFromData(int $id, int $stateMeta) : void{
        $this->composter_fill_level = BlockDataSerializer::readBoundedInt("composter_fill_level", $stateMeta, 0, 8);
    }

    public function getStateBitmask() : int{
        return 0b1111;
    }

    protected function recalculateCollisionBoxes() : array{
        $boxes = [$this->getSideCollisionBox(Facing::DOWN)];
        foreach (Facing::HORIZONTAL as $side) $boxes[] = $this->getSideCollisionBox($side);
        return $boxes;
    }

    protected function getSideCollisionBox(int $face = Facing::NORTH) : AxisAlignedBB{
        $empty = abs(15 - 2*$this->composter_fill_level) - ($this->composter_fill_level === 0);
        return ($face === Facing::DOWN || $face === Facing::UP) ? AxisAlignedBB::one()->contract(2/16, 0, 2/16)->trim(Facing::UP, $empty/16) : AxisAlignedBB::one()->trim(Facing::opposite($face), 14/16);
    }

    public function isEmpty() : bool{
        return $this->composter_fill_level === 0;
    }

    public function isReady() : bool{
        return $this->composter_fill_level === 8;
    }

    public function getComparatorOutput(): int{
        return $this->composter_fill_level;
    }

    /**
     * @throws \Exception
     */
    public function onInteract(Item $item, int $face, Vector3 $clickVector, ?Player $player = null): bool{
        if ($player instanceof Player && !$player->isSneaking()) $this->compost($player, $item);
        return true;
    }

    public function pushCollidedEntities() : void{
        foreach (
            $this->position->getWorld()->getNearbyEntities(
                /** new AxisAlignedBB(
                    $this->position->getFloorX(),
                    $this->position->getFloorY(),
                    $this->position->getFloorZ(),
                    $this->position->getFloorX() + 1,
                    $this->position->getFloorY() + 1,
                    $this->position->getFloorZ() + 1
                )*/
                $this->getSideCollisionBox(Facing::DOWN)->extend(Facing::UP, 3/16)->offset(
                    $this->position->getFloorX(),
                    $this->position->getFloorY(),
                    $this->position->getFloorZ()
                )
            ) as $entity) {
            if ($entity instanceof Player || $entity instanceof Projectile) continue;
            print($entity::class . "\n");
            $motion = $entity->getMotion();
            var_dump($motion);

            print("new \n");
            $motion->y += 0.19;
            if ($entity instanceof ItemEntity) {
                $motion->y += 0.2 + 0.125; //Broken at 1, 2, 3 and maybe 4 fill level (For item entity only) -> maybe their offset is different or they don't have contact
            }
            if ($motion->y >= 0.4) $motion->y = 0.4;
            if ($motion->y <= -0.4) $motion->y = -0.4;
            var_dump($motion);

            print("end \n");
            $entity->setMotion($motion);
        }
    }
    /**
     * @throws \Exception
     */
    public function compost(Block | Player $origin, ?Item $item = null) : bool{
        if ($this->composter_fill_level >= 8) {
            $event = new ComposterEmptyEvent($origin, $this, [(new Item(new ItemIdentifier(351, 15), "Bone Meal"))->setCount(1)]);
            $event->call();
            if ($event->isCancelled()) return false;

            $this->position->getWorld()->addSound($this->position, new ComposterEmptySound());
            for ($i = 0; $i < 40; $i++) $this->position->getWorld()->addParticle($this->position->add(0.5 - sin(deg2rad(mt_rand(-45, 45))) / 2, 0.5 + mt_rand(-1, 10) / 16, 0.5 - sin(deg2rad(mt_rand(-45, 45))) / 2), new HappyVillagerParticle());

            $block = $this->position->getWorld()->getBlock($this->position->getSide(Facing::DOWN));
            if ($block instanceof Hopper) {
                $block->getInventory()->addItem((new Item(new ItemIdentifier(351, 15))));
            } else {
                $this->position->getWorld()->dropItem($this->position->add(0.5, 0.85, 0.5), (new Item(new ItemIdentifier(351, 15), "Bone Meal"))->setCount(1), new Vector3(sin(deg2rad(mt_rand(-15, 15))) / 100, sin(deg2rad(mt_rand(0, 15))/100), sin(deg2rad(mt_rand(-15, 15))) / 100));
            }

            $this->composter_fill_level = 0;
        } else {
            if (!ComposterUtils::isCompostable($item)) return false;
            $percent = ComposterUtils::getPercentage($item);

            if (mt_rand(1, 100) <= $percent) {
                $event = new ComposterFillEvent($origin, $this, true);
                $event->call();
                if ($event->isCancelled()) return false;
                $this->pushCollidedEntities();
                ++$this->composter_fill_level;
                if ($this->composter_fill_level === 8) {
                    $event = new ComposterReadyEvent($origin, $this);
                    $event->call();
                    if ($event->isCancelled()) {
                        --$this->composter_fill_level; //Bad solution
                        return false;
                    }
                    $this->position->getWorld()->addSound($this->position, new ComposterReadySound());
                } else {
                    $this->position->getWorld()->addSound($this->position, new ComposterFillSuccessSound());
                }
                for ($i = 0; $i < 30; $i++) $this->position->getWorld()->addParticle($this->position->add(0.5 - sin(deg2rad(mt_rand(-45, 45))) / 2, ($this->composter_fill_level + mt_rand(2, 9)) / 16, 0.5 - sin(deg2rad(mt_rand(-45, 45))) / 2), new HappyVillagerParticle());
            } else {
                $event = new ComposterFillEvent($origin, $this);
                $event->call();
                if ($event->isCancelled()) return false;

                $this->position->getWorld()->addSound($this->position, new ComposterFillSound());
            }
            $item->pop();
        }
        $this->position->getWorld()->setBlock($this->position, $this);
        return true;
    }

    public function getDrops(Item $item): array{
        return $this->composter_fill_level === 8 ? [
            (new Composter(new BlockIdentifier(BlockLegacyIds::COMPOSTER, 0), "Composter", new BlockBreakInfo(0.6, BlockToolType::AXE)))->asItem(),
            new Item(new ItemIdentifier(351, 15), "Bone Meal")
        ] : [
            (new Composter(new BlockIdentifier(BlockLegacyIds::COMPOSTER, 0), "Composter", new BlockBreakInfo(0.6, BlockToolType::AXE)))->asItem()
        ];
    }

    public function getComposterFillLevel() : int{
        return $this->composter_fill_level;
    }

    public function getFlameEncouragement() : int{
        return 5;
    }

    public function getFlammability() : int{
        return 20;
    }

    public function getFuelTime() : int{
        return 50;
    }
}