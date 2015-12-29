<?php

namespace Kanboard\Plugin\Jabber;

require_once __DIR__.'/vendor/autoload.php';

use Kanboard\Core\Translator;
use Kanboard\Core\Plugin\Base;

/**
 * Jabber Plugin
 *
 * @package  jabber
 * @author   Frederic Guillot
 */
class Plugin extends Base
{
    public function initialize()
    {
        $this->template->hook->attach('template:config:integrations', 'jabber:config/integration');
        $this->template->hook->attach('template:project:integrations', 'jabber:project/integration');
        $this->template->hook->attach('template:user:integrations', 'jabber:user/integration');

        $this->userNotificationType->setType('jabber', t('Jabber'), '\Kanboard\Plugin\Jabber\Notification\Jabber');
        $this->projectNotificationType->setType('jabber', t('Jabber'), '\Kanboard\Plugin\Jabber\Notification\Jabber');

        $this->on('app.bootstrap', function($container) {
            Translator::load($container['config']->getCurrentLanguage(), __DIR__.'/Locale');
        });
    }

    public function getPluginDescription()
    {
        return 'Receive notifications on Jabber';
    }

    public function getPluginAuthor()
    {
        return 'Frédéric Guillot';
    }

    public function getPluginVersion()
    {
        return '1.0.3';
    }

    public function getPluginHomepage()
    {
        return 'https://github.com/kanboard/plugin-jabber';
    }
}
