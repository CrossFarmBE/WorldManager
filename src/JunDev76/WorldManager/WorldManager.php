<?php

/*
       _             _____           ______ __
      | |           |  __ \         |____  / /
      | |_   _ _ __ | |  | | _____   __ / / /_
  _   | | | | | '_ \| |  | |/ _ \ \ / // / '_ \
 | |__| | |_| | | | | |__| |  __/\ V // /| (_) |
  \____/ \__,_|_| |_|_____/ \___| \_//_/  \___/


This program was produced by JunDev76 and cannot be reproduced, distributed or used without permission.

Developers:
 - JunDev76 (https://github.jundev.me/)

Copyright 2022. JunDev76. Allrights reserved.
*/

namespace JunDev76\WorldManager;

use JunDev76\WorldManager\Generator\VoidGenerator;
use JunKR\CrossUtils;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\permission\DefaultPermissions;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\world\generator\Flat;
use pocketmine\world\generator\GeneratorManager;
use pocketmine\world\WorldCreationOptions;

class WorldManager extends PluginBase{

    protected function onLoad() : void{
        GeneratorManager::getInstance()->addGenerator(VoidGenerator::class, 'void', fn() => null);
    }

    protected function onEnable() : void{
        CrossUtils::registercommand('mw', $this, '월드관리자', DefaultPermissions::ROOT_OPERATOR);
    }

    protected function moveWorld(Player $player, string $worldName) : void{
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        if($world === null){
            $this->getServer()->getWorldManager()->loadWorld($worldName, true);
            $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        }

        $player->teleport($world->getSafeSpawn());
        $player->sendMessage('이동완료.');
    }

    protected function unloadWorld(Player $player, string $worldName) : void{
        $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        if($world === null){
            $this->getServer()->getWorldManager()->loadWorld($worldName, true);
            $world = $this->getServer()->getWorldManager()->getWorldByName($worldName);
        }

        foreach($world->getPlayers() as $players){
            $this->getServer()->dispatchCommand($players, '스폰');
            $players->sendMessage('§a§l[시스템] §r§7관리자에 의해 해당 구역이 비활성화 되었습니다.');
        }

        $this->getServer()->getWorldManager()->unloadWorld($world, true);
        $player->sendMessage('언로드 완료');
    }

    protected function generateWorld(Player $player, string $worldName) : void{
        if(realpath($this->getServer()->getDataPath() . 'worlds/' . $worldName)){
            $player->sendMessage('이미 있는 월드');
            return;
        }
        $creationOptions = WorldCreationOptions::create();
        $creationOptions->setSeed(0);
        $creationOptions->setSpawnPosition(new Vector3(0, 4, 0));
        $creationOptions->setGeneratorClass(Flat::class);
        $this->getServer()->getWorldManager()->generateWorld($worldName, $creationOptions, true);
        $player->sendMessage('완료');
    }

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
        if($sender instanceof Player && $command->getName() === 'mw'){
            if(!isset($args[0])){
                return true;
            }
            if(!isset($args[1])){
                return true;
            }

            switch($args[0]){
                case 'move':
                case '이동':
                    $this->moveWorld($sender, $args[1]);
                    break;
                case 'unload':
                    $this->unloadWorld($sender, $args[1]);
                    break;
                case 'create':
                case 'generate':
                    $this->generateWorld($sender, $args[1]);
                    break;
            }
        }
        return true;
    }

}