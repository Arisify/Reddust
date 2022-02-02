<?php
declare(strict_types=1);
namespace arie\reddust\listener;

use arie\reddust\block\inventory\IWindowType;
use pocketmine\event\inventory\InventoryOpenEvent;
use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\inventory\Inventory;
use pocketmine\network\mcpe\protocol\ContainerOpenPacket;

class InventoryListener implements Listener{
    private ?Inventory $lastInventory = null;

    public function onInventoryOpen(InventoryOpenEvent $event): void {
        $inventory = $event->getInventory();
        $this->lastInventory = $inventory instanceof IWindowType ? $inventory : null;
    }

    public function onDataPacketSend(DataPacketSendEvent $event): void {
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