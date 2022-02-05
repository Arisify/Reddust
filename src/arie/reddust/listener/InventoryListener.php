<?php
declare(strict_types=1);
namespace arie\reddust\listener;

use arie\reddust\block\Hopper;
use arie\reddust\block\inventory\IWindowType;
use pocketmine\block\inventory\BlockInventory;
use pocketmine\block\inventory\DoubleChestInventory;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\Inventory;
use pocketmine\math\Facing;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

class InventoryListener implements Listener{
    private ?Inventory $lastInventory = null;

	private const ALL = [
		-1,
		Facing::DOWN,
		Facing::UP,
		Facing::NORTH,
		Facing::SOUTH,
		Facing::WEST,
		Facing::EAST
	];

    public function onInventoryOpen(InventoryOpenEvent $event) : void {
        $inventory = $event->getInventory();
        $this->lastInventory = $inventory instanceof IWindowType ? $inventory : null;
    }

	public function onInventoryTransition(InventoryTransactionEvent $event) : void{
		foreach ($event->getTransaction()->getInventories() as $inventory) {
			if ($inventory instanceof BlockInventory) {
				$position = $inventory->getHolder();
				if (!$position->isValid()) {
					continue;
				}
				foreach (self::ALL as $face) {
					$block = $position->getWorld()->getBlock($position->getSide($face));
					if ($block instanceof Hopper) {
						$block->reschedule();
					}
				}
				if ($inventory instanceof DoubleChestInventory) {
					$position = $inventory->getRightSide()->getHolder();
					foreach (self::ALL as $face) {
						$block = $position->world->getBlock($position->getSide($face));
						if ($block instanceof Hopper) {
							$block->reschedule();
						}
					}
				}
			}
		}
	}

    public function onDataPacketSend(DataPacketSendEvent $event) : void {
        $packets = $event->getPackets();

        foreach ($packets as $packet) {
            if (!$packet instanceof ContainerOpenPacket) {
                continue;
            }
            $inventory = $this->lastInventory;
            $this->lastInventory = null;
            $type = $inventory instanceof IWindowType ? $inventory->getWindowType() : null;
            if ($type !== null) {
                $packet->windowType = $type;
            }
        }
    }
}