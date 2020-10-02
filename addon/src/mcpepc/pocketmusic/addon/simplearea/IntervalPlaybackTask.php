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
use function is_array;
use function is_string;
use function preg_match;
use function substr;

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
		$soundName = $this->soundName ?? $this->getPlugin()->getPocketMusic()->getAutoPlaySound($this->player->getLevel());

		if (strpos($soundName, 'pocketmusic.') === 0 && strpos($soundName, 'pocketmusic.music.') === false) {
			$soundName = $this->getResourcePackConfig()->get('soundsCache');
		}

		if (is_array($soundName)) {
			$soundName = $soundName[array_rand($soundName)];
		}

		if (!is_string($soundName)) {
			return;
		}

		if (strpos($soundName, 'pocketmusic.music.') === 0) {
			$soundName = substr($soundName, 18);
		}

		if (!preg_match('/^[a-z]+$/', $soundName)) {
			$this->getPlugin()->getLogger()->error('PocketMusic 설정이 SimpleArea 애드온과 호환되지 않아요. 개발자 MCPE_PC에게 문의해주세요.');
			$this->getPlugin()->getServer()->stop();
		}

		if ($this->player->isOnline()) {
			$this->getPlugin()->getPocketMusic()->playSound(false, true, $this->player, $this->soundName);
			$this->getPlugin()->ready($this->player, ($this->getPlugin()->getPocketMusic()->getSoundInfo($soundName)['duration')] * 20);
		}
	}
}
