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
$this->registerModule(
    'Notices',
    'Store all or error only notices in the database',
    'Franck Paul and contributors',
    '7.0',
    [
        'date'        => '2026-04-29T14:25:25+0200',
        'requires'    => [['core', '2.38']],
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
