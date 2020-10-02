<?php

namespace mcpepc\pocketmusic\addon\simplearea;

use ifteam\SimpleArea\database\area\AreaProvider;
use ifteam\SimpleArea\database\area\AreaSection;
use mcpepc\pocketmusic\PocketMusic;
use pocketmine\event\Listener;
use pocketmine\level\Level;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use function ksort;
use function strtolower;

class PocketMusicAddonSimpleArea extends PluginBase implements Listener {
	private $areaConfig;

	private $delayedTasks = [];
	
	private $whereIs = [];

	function onLoad(): void {
		$this->areaConfig = new Config($this->getDataFolder() . 'areas.json');
	}

	function onEnable() {
		$this->getLogger()->info('이 플러그인 사용 시에 메모리가 부족해질 수 있으니 꼭 하루에 한 번 이상 서버를 재시작해주세요! :) by. MCPE_PC');

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	function onDisable(): void {
		$this->areaConfig->setAll(ksort($this->areaConfig->getAll()));
		$this->areaConfig->save();
	}

	function onJoin(PlayerJoinEvent $event) {}

	// https://github.com/organization/SimpleArea/blob/master/src/ifteam/SimpleArea/EventListener.php
	function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		$posX = $player->getLocation()->getFloorX();
		$posZ = $player->getLocation()->getFloorZ();
		$world = $player->getWorld()->getFolderName();
		$playerName = strtolower($player->getName());
		$area = (AreaProvider::getInstance())->getArea($world, $posX, $posZ, $playerName);

		if ($area instanceof AreaSection) {
			$key = $world . ' ' . $area->getId();
			if (($player->isOp() || !$area->isAccessDeny() || $area->isResident($user)) && $this->whereIs[$playerName] === $key) {
				$this->whereIs[$playerName] = $key;
				$this->ready($player, -1, $this->areaConfig->get($key, null));
			}
		} else {
			$this->playerExitsArea($player);
		}
	}

	function onEntityLevelChange(EntityLevelChangeEvent $event) {}

	private function playerExitsArea(Player $player) {
		$name = strtolower($player->getName());

		if ($this->whereIs[$name] !== null) {
			$this->whereIs[$name] = null;
			$this->ready($player);
		}
	}

	function ready(Player $player, int $delay = -1, $soundName = null): void {
		if (isset($this->delayedTasks[strtolower($player->getName())]) {
			$this->delayedTasks[strtolower($player->getName())]->getHandler()->cancel();
		}

		$this->delayedTasks[strtolower($player->getName())] = $this->getScheduler()->scheduleDelayedTask(new IntervalPlaybackTask($this, $player, $soundName), $delay);
	}

	function setAreaSound(Level $world, int $id, $soundName): void {
		$this->areaConfig->set($world->getFolderName() . ' ' . $id, $soundName);
		$this->areaConfig->save();
	}

	function getPocketMusic(): ?PocketMusic {
		return $this->getServer()->getPluginManager()->getPlugin('PocketMusic');
	}
}
