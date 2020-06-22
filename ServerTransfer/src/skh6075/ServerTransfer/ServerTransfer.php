<?php

namespace skh6075\ServerTransfer;

use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use skh6075\ServerTransfer\entity\TransferEntity;
use skh6075\ServerTransfer\command\ServerTransferCommand;
use skh6075\ServerTransfer\lang\PluginLang;

class ServerTransfer extends PluginBase{

    private static $instance = null;

    protected $lang = null;



    public static function getInstance (): ?ServerTransfer{
        return self::$instance;
    }

    public function onLoad (): void{
        self::$instance = $this;
        Entity::registerEntity(TransferEntity::class, true, [ 'TransferEntity ']);
        
        array_map (function (string $langData): void{
            $this->saveResource($langData . ".yml", true);
        }, [ 'kor', 'eng' ]);

        $language = (new Config(\pocketmine\DATA . 'server.properties', Config::PROPERTIES))->get ('language');
        $this->lang = new PluginLang ($this, $language);
    }

    public function onEnable (): void{
        $this->getServer()
            ->getCommandMap()
            ->register ('skh6075', new ServerTransferCommand ($this->lang->translateString ('command.name', [], false), $this->lang->translateString ('command.description', [], false)));
    }

    public function getLang (): PluginLang{
        return $this->lang;
    }

    public function spawnTransferEntity (Player $player, string $serverName, string $serverIp, int $port = 19132): void{
        $nbt = Entity::createBaseNBT($player->asVector3(), null, $player->yaw, $player->pitch);
        $nbt->setTag (new CompoundTag('Skin', [
            new StringTag('Name', $player->getSkin()->getSkinId()),
            new ByteArrayTag('Data', $player->getSkin()->getSkinData())
        ]));
        $nbt->setString('serverName', $serverName);
        $nbt->setString('serverIp', $serverIp);
        $nbt->setInt('serverPort', $port);
        $entity = Entity::createEntity('TransferEntity', $player->level, $nbt);
        $entity->setNameTag ("§l§f{$serverName}\nSetting....");
        $entity->setNameTagAlwaysVisible(true);
        $entity->setScale(2);
    }
}