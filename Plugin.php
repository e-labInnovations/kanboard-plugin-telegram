<?php

namespace Kanboard\Plugin\Telegram;

require_once __DIR__ . '/vendor/autoload.php';

use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;
use Kanboard\Plugin\Telegram\TelegramHelper;

/**
 * Telegram Plugin
 *
 * @package  telegram
 * @author   Manu Varkey
 * @author   Mohammed Ashad
 */
class Plugin extends Base {
    public function initialize() {
        $this->template->hook->attach('template:config:integrations', 'telegram:config/integration');
        $this->template->hook->attach('template:project:integrations', 'telegram:project/integration', array('bot_name' =>  $this->configModel->get('telegram_username')));
        $this->template->hook->attach('template:user:integrations', 'telegram:user/integration', array('bot_name' => $this->configModel->get('telegram_username')));

        $this->userNotificationTypeModel->setType('telegram', t('Telegram'), '\Kanboard\Plugin\Telegram\Notification\Telegram');
        $this->projectNotificationTypeModel->setType('telegram', t('Telegram'), '\Kanboard\Plugin\Telegram\Notification\Telegram');

        // Helper
        $this->helper->register('TelegramHelper', '\Kanboard\Plugin\Telegram\Helper\TelegramHelper');
    }

    public function onStartup() {
        Translator::load($this->languageModel->getCurrentLanguage(), __DIR__ . '/Locale');
    }

    public function getPluginDescription() {
        return 'Receive notifications on Telegram';
    }

    public function getPluginAuthor() {
        return 'Manu Varkey';
    }

    public function getPluginVersion() {
        return '1.6.0';
    }

    public function getPluginHomepage() {
        return 'https://github.com/manuvarkey/plugin-telegram';
    }

    public function getCompatibleVersion() {
        return '>=1.2.22';
    }
}
