<?php

/**
 * @brief logNotices, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
$this->registerModule(
    'logNotices',
    'Store all or error only notices in the database',
    'Franck Paul and contributors',
    '4.3',
    [
        'date'        => '2025-02-22T18:27:09+0100',
        'requires'    => [['core', '2.28']],
        'permissions' => 'My',
        'type'        => 'plugin',
        'settings'    => [
            'blog' => '#params.logNotices',
        ],

        'details'    => 'https://open-time.net/?q=logNotices',
        'support'    => 'https://github.com/franck-paul/logNotices',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/logNotices/main/dcstore.xml',
    ]
);
