<?php

namespace skh6075\ServerTransfer\command;

use pocketmine\entity\Entity;
use pocketmine\nbt\tag\ByteArrayTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use skh6075\ServerTransfer\lang\PluginLang;
use skh6075\ServerTransfer\ServerTransfer;

class ServerTransferCommand extends Command{

    private ServerTransfer $plugin;

    public function __construct(ServerTransfer $plugin) {
        parent::__construct(PluginLang::getInstance()->format("command.name", [], false), PluginLang::getInstance()->format("command.description", [], false));
        $this->setPermission("servertransfer.permission");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $player, string $commandLabel, array $args): bool{
        if (!$player instanceof Player) {
            $player->sendMessage(PluginLang::getInstance()->format('command.use.only.ingame'));
            return false;
        }

        if (!$this->testPermission($player)) {
            return false;
        }

        $name = array_shift($args) ?? "";
        $address = array_shift($args) ?? "";
        $port = array_shift($args) ?? "";
        if (trim($name) === "" or trim($address) === "" or trim($port) === "" or !is_numeric($port)) {
            $player->sendMessage(PluginLang::getInstance()->format("failed.spawn.transferentity"));
            return false;
        }

        $nbt = Entity::createBaseNBT($player->asVector3(), null, $player->getYaw(), $player->getPitch());
        $nbt->setTag(new CompoundTag("Skin", [
            new StringTag("Name", $player->getSkin()->getSkinId()),
            new ByteArrayTag("Data", $player->getSkin()->getSkinData()),
            new ByteArrayTag("CapeData", $player->getSkin()->getCapeData()),
            new StringTag("GeometryName", $player->getSkin()->getGeometryName()),
            new ByteArrayTag("GeometryData", $player->getSkin()->getGeometryData())
        ]));
        $nbt->setString("serverName", $name);
        $nbt->setString("serverIp", $address);
        $nbt->setInt("serverPort", $port);

        $entity = Entity::createEntity("TransferEntity", $player->getLevel(), $nbt);
        $entity->spawnToAll();
        $entity->setNameTag("§l§f{$name}\nSetting....");
        $entity->setNameTagAlwaysVisible(true);

        $player->sendMessage(PluginLang::getInstance()->format("successed.spawn.transferentity"));
        return true;
    }
}