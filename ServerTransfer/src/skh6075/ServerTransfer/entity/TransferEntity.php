<?php

namespace skh6075\ServerTransfer\entity;

use pocketmine\entity\Entity;
use pocketmine\entity\Human;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use skh6075\ServerTransfer\query\ServerQuery;
use pocketmine\Player;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\scheduler\Task;
use skh6075\ServerTransfer\ServerTransfer;

class TransferEntity extends Human {

    /** @var string */
    protected $serverName;

    /** @var string */
    protected $serverIp;

    /** @var int */
    protected $serverPort;

    /** @var int */
    protected $replace_query_queue = 0;

    /** @var bool */
    protected $server_online = false;


    public function __construct (Level $level, CompoundTag $nbt) {
        parent::__construct ($level, $nbt);
    }

    public function initEntity (): void{
        parent::initEntity ();
        $nbt = $this->namedtag;

        if (!$nbt->hasTag ('serverName', StringTag::class)) {
            $this->close ();
            return;
        }
        if (!$nbt->hasTag ('serverIp', StringTag::class)) {
            $this->close ();
            return;
        }
        if (!$nbt->hasTag ('serverPort', IntTag::class)) {
            $this->close ();
            return;
        }
        $this->serverName = $nbt->getString('serverName');
        $this->serverIp = $nbt->getString('serverIp');
        $this->serverPort = $nbt->getInt('serverPort');
    }

    public function saveNBT (): void{
        parent::saveNBT ();
        $this->namedtag->setString('serverName', $this->serverName);
        $this->namedtag->setString('serverIp', (string) $this->serverIp);
        $this->namedtag->setInt('serverPort', $this->serverPort);
    }

    public function onUpdate (int $currentTick): bool{
        if (!$this->isClosed()) {
            $this->replace_query_queue++;
            if ($this->replace_query_queue >= 50) {
                $this->replaceQuery($this);
                $this->setScale (2);
                $this->setNameTagAlwaysVisible(true);
            }
        }
        parent::onUpdate($currentTick);
        return false;
    }

    public function replaceQuery (Entity &$entity): void{
        if ($entity->isAlive ()) {
            $queryFaction = new ServerQuery ($this->serverIp, $this->serverPort);
            if ($queryFaction->check ()) {
                $entity->setNameTag ("§l§f{$this->serverName}\n§fOnline: §a" . $queryFaction->getNumPlayer ());
                $this->server_online = true;
            } else {
                $entity->setNameTag ("§l§f{$this->serverName}\nOffline Server.");
                $this->server_online = false;
            }
        }
    }

    public function attack (EntityDamageEvent $event): void{
        if ($event instanceof EntityDamageByEntityEvent) {
            $event->setCancelled ();
            if (($player = $event->getDamager ()) instanceof Player) {
                if ($this->server_online) {
                    $this->transferServer ($player);
                } else {
                    $player->sendMessage (ServerTransfer::getInstance ()->getLang ()->translateString ('server.not.found'));
                }
            }
        }
    }

    public function transferServer (Player $player): void{
        $player->addTitle (
            ServerTransfer::getInstance ()->getLang ()->translateString ('server.move.title', [
                '%name%' => $this->serverName
            ], false),
            ServerTransfer::getInstance ()->getLang ()->translateString ('server.move.subTitle', [], false)
        );
        ServerTransfer::getInstance ()->getScheduler ()->scheduleDelayedTask (new class ($player, $this->serverIp, $this->serverPort) extends Task{
            protected $player;
            protected $ip;
            protected $port;

            public function __construct (Player $player, string $ip, int $port) {
                $this->player = $player;
                $this->ip = $ip;
                $this->port = $port;
            }

            public function onRun (int $currentTick): void{
                if ($this->player->isOnline ()) {
                    $this->player->transfer ($this->ip, $this->port);
                } else {
                    $this->getHandler ()->cancel ();
                }
            }
        }, 25 * 5);
    }
}
