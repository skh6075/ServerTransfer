<?php

namespace skh6075\ServerTransfer\command;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\permission\Permission;
use skh6075\ServerTransfer\ServerTransfer;

class ServerTransferCommand extends Command{


    public function __construct (string $name, string $description) {
        parent::__construct ($name, $description);
        $this->setPermission(Permission::DEFAULT_OP);
    }

    public function execute(CommandSender $player, string $commandLabel, array $args): bool{
        if (!$player instanceof Player) {
            $player->sendMessage(ServerTransfer::getInstance()->getLang()->translateString('command.use.only.ingame'));
            return false;
        }
        if (!$player->hasPermission($this->getPermission())) {
            $player->sendMessage(ServerTransfer::getInstance()->getLang()->translateString('dont.have.permission'));
            return false;
        }
        if (!isset ($args [0]) or !isset ($args [1]) or !isset ($args [2]) or !is_numeric($args [2])) {
            $player->sendMessage(ServerTransfer::getInstance()->getLang()->translateString('failed.spawn.transferentity'));
            return false;
        }
        ServerTransfer::getInstance()->spawnTransferEntity ($player, $args [0], $args [1], $args [2]);
        $player->sendMessage (ServerTransfer::getInstance()->getLang()->translateString('successed.spawn.transferentity'));
        return true;
    }
}