<?php

namespace mcpepc\pocketmusic\addon\simplearea;

use ifteam\SimpleArea\database\area\AreaProvider;
use ifteam\SimpleArea\database\area\AreaSection;
use mcpepc\pocketmusic\PocketMusic;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
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
		$this->saveResource('areas.json');
		$this->areaConfig = new Config($this->getDataFolder() . 'areas.json');
	}

	function onEnable(): void {
		$this->getLogger()->info('이 플러그인 사용 시에 메모리가 부족해질 수 있으니 꼭 하루에 한 번 이상 서버를 재시작해주세요! :) by. MCPE_PC');

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	function onDisable(): void {
		$areaConfig = $this->areaConfig->getAll();
		ksort($areaConfig);
		$this->areaConfig->setAll($areaConfig);
		$this->areaConfig->save();
	}

	/**
	 * @priority HIGH
	 */
	function onJoin(PlayerJoinEvent $event) {
		$player = $event->getPlayer();
		$this->whereIs[strtolower($player->getName())] = null;
		$this->ready($player);
	}

	// https://github.com/organization/SimpleArea/blob/master/src/ifteam/SimpleArea/EventListener.php
	function onPlayerMove(PlayerMoveEvent $event) {
		$player = $event->getPlayer();
		$posX = $player->getLocation()->getFloorX();
		$posZ = $player->getLocation()->getFloorZ();
		$world = $player->getLevel()->getFolderName();
		$playerName = strtolower($player->getName());
		$area = (AreaProvider::getInstance())->getArea($world, $posX, $posZ, $playerName);

		if ($area instanceof AreaSection && ($player->isOp() || !$area->isAccessDeny() || $area->isResident($user)) && $this->whereIs[$playerName] !== ($key = $world . ' ' . $area->getId())) {
			$this->whereIs[$playerName] = $key;
			$this->ready($player);
		} else if ($this->playerExitsArea($player)) {
				$this->ready($player);
		}
	}

	/**
	 * @priority HIGH
	 */
	function onEntityLevelChange(EntityLevelChangeEvent $event) {
		$player = $event->getEntity();

		if ($player instanceof Player) {
			$this->playerExitsArea($player);
			$this->ready($player);
		}
	}

	private function playerExitsArea(Player $player): bool {
		$player = strtolower($player->getName());

		if ($this->whereIs[$player] !== null) {
			$this->whereIs[$player] = null;
			return true;
		}

		return false;
	}

	function ready(Player $player, int $delay = -1, $soundName = null): void {
		$playerName = strtolower($player->getName());
		if (isset($this->delayedTasks[$playerName])) {
			$this->delayedTasks[$playerName]->cancel();
		}

		if ($soundName === null && $this->whereIs[$playerName] !== null) {
			$soundName = $this->areaConfig->get($key, null);
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
