<?php

namespace skh6075\ServerTransfer;

use pocketmine\entity\Entity;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\SingletonTrait;
use skh6075\ServerTransfer\entity\TransferEntity;
use skh6075\ServerTransfer\command\ServerTransferCommand;
use skh6075\ServerTransfer\lang\PluginLang;

final class ServerTransfer extends PluginBase{
    use SingletonTrait;

    private static ?PluginLang $lang;

    public function onLoad(): void{
        self::setInstance($this);

        Entity::registerEntity(TransferEntity::class, true, ['TransferEntity']);
    }

    public function onEnable(): void{
        $this->saveResource("lang/kor.yml");
        $this->saveResource("lang/eng.yml");
        self::$lang = PluginLang::getInstance()->setProperties($language = $this->getServer()->getLanguage()->getLang(), yaml_parse(file_get_contents($this->getDataFolder() . "lang/" . $language . ".yml")));

        $this->getServer()->getCommandMap()->register(strtolower($this->getName()), new ServerTransferCommand($this));
    }
}
