<?php

namespace skh6075\ServerTransfer\entity;

use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\scheduler\ClosureTask;
use skh6075\ServerTransfer\lang\PluginLang;
use skh6075\ServerTransfer\query\ServerQuery;
use pocketmine\Player;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use skh6075\ServerTransfer\ServerTransfer;

final class TransferEntity extends Human{

    private string $serverName;

    private int $replace_query_queue = 0;

    private int $check_server = 0;

    private bool $server_online = false;

    private ServerQuery $query;

    public function __construct (Level $level, CompoundTag $nbt) {
        parent::__construct ($level, $nbt);
    }

    public function initEntity (): void{
        parent::initEntity ();

        $nbt = $this->namedtag;
        $this->serverName = $nbt->getString('serverName', "");
        $address = $nbt->getString('serverIp', "");
        $port = $nbt->getInt('serverPort', -1);

        $this->query = new ServerQuery($address, $port);
    }

    public function saveNBT (): void{
        parent::saveNBT ();
        $this->namedtag->setString('serverName', $this->serverName);
        $this->namedtag->setString('serverIp', $this->query->getServerAddress());
        $this->namedtag->setInt('serverPort', $this->query->getServerPort());
    }

    public function onUpdate (int $currentTick): bool{
        if (!$this->isClosed()) {
            $this->replace_query_queue++;
            if ($this->replace_query_queue >= 50) {
                $this->replaceQuery();
                $this->setScale (2);
                $this->setNameTagAlwaysVisible(true);
            }

            if (!$this->server_online) {
                $this->check_server ++;
                $this->query->onUpdateQuery();
                $this->server_online = $this->query->isConnect();
                if ($this->check_server >= 250) {
                    $this->close();
                    return true;
                }
            }
        }

        return parent::onUpdate($currentTick);
    }

    public function replaceQuery(): void{
        if (!$this->isAlive () or !$this->isClosed())
            return;

        if ($this->query->isConnect()) {
            $this->setNameTag("§l§f{$this->serverName}§r\n§fOnline: §a" . $this->query->getNumPlayer());
        } else {
            $this->setNameTag("§l§f{$this->serverName}§r\nOffline Server.");
        }

        $this->server_online = $this->query->isConnect();
    }

    public function attack (EntityDamageEvent $event): void{
        if (!$event instanceof EntityDamageByEntityEvent)
            return;

        $event->setCancelled();
        /** @var Player $player */
        if (!($player = $event->getDamager()) instanceof Player)
            return;

        if ($this->server_online) {
            $this->transferServer($player);
        } else {
            $player->sendMessage(PluginLang::getInstance()->format("server.not.found"));
        }
    }

    public function transferServer (Player $player): void{
        $titles = [
            PluginLang::getInstance()->format("server.move.title", ["%name%" => $this->serverName], false),
            PluginLang::getInstance()->format("server.move.subTitle", [], false)
        ];

        $player->sendTitle($titles[0], $titles[1]);

        ServerTransfer::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void{
            if ($player->isOnline()) {
                $player->transfer($this->query->getServerAddress(), $this->query->getServerPort());
            }
        }), 25 * 5);
    }
}
