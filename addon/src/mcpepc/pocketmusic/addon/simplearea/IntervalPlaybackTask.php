<?php
/**
 * @author MCPE_PC
 * @description Modified code of PlaySoundTask
 * @refer mcpepc\pocketmusic\tasks\PlaySoundTask
 */

namespace mcpepc\pocketmusic\addon\simplearea;

use mcpepc\pocketmusic\PocketMusic;
use mcpepc\pocketmusic\Sound;
use mcpepc\pocketmusic\tasks\PlaySoundTask;
use mcpepc\pocketmusic\tasks\PocketMusicTask;
use pocketmine\plugin\Plugin;
use function strlen;
use function strpos;
use function substr;

class IntervalPlaybackTask extends PocketMusicTask {
	private $args = [];

	function __construct(Plugin $plugin, ...$args) {
		$this->owningPlugin = $plugin;
		$this->args = $args;
	}

	function onRun(int $currentTick) {
		$this->getPlugin()->getScheduler()->scheduleTask(new PlaySoundTask($this->getPlugin()->getPocketMusic(), ...$args));
		$this->getPlugin()->ready($player);
	}
}
