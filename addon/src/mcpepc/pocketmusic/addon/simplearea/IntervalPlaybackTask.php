<?php
/**
 * @author MCPE_PC
 * @description Modified code of PlaySoundTask
 * @refer mcpepc\pocketmusic\tasks\PlaySoundTask
 */

namespace mcpepc\pocketmusic\addon\simplearea;

use mcpepc\pocketmusic\tasks\PocketMusicTask;
use pocketmine\Player;
use pocketmine\plugin\Plugin;

class IntervalPlaybackTask extends PocketMusicTask {
	private $player;

	private $soundName = null;

	function __construct(Plugin $plugin, Player $player, $soundName = null) {
		$this->owningPlugin = $plugin;
		$this->player = $player;

		if ($soundName !== null) {
			$this->soundName = $soundName;
		}
	}

	function onRun(int $currentTick) {
		$this->getPlugin()->getPocketMusic()->playSound(false, false, $player, $this->soundName ?? $this->getPlugin()->getPocketMusic()->getAutoPlaySound($player->getLevel()));
		$this->getPlugin()->ready($player);
	}
}
