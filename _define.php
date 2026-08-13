<?php

/**
 * @brief logNotices, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

if (isset($this) && is_object($this) && method_exists($this, 'registerModule') && isset($this->id) && is_string($this->id)) {
    $this->registerModule(
        'Notices',
        'Store all or error only notices in the database',
        'Franck Paul and contributors',
        '8.0',
        [
            'date'        => '2026-08-03T10:03:27+0200',
            'requires'    => [['core', '2.39']],
            'permissions' => 'My',
            'type'        => 'plugin',
            'settings'    => [
                'blog' => '#params.logNotices',
            ],

            'details'    => 'https://open-time.net/?q=logNotices',
            'support'    => 'https://github.com/franck-paul/logNotices',
            'repository' => 'https://raw.githubusercontent.com/franck-paul/logNotices/main/dcstore.xml',
            'license'    => 'gpl2',
        ]
    );
}
