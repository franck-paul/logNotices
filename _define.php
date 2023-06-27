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
    'Store notices in log table',
    'Store all or error only notices in the database',
    'Franck Paul and contributors',
    '2.1.1',
    [
        'requires'    => [['core', '2.26']],
        'permissions' => dcCore::app()->auth->makePermissions([
            dcAuth::PERMISSION_USAGE,
            dcAuth::PERMISSION_CONTENT_ADMIN,
        ]),
        'type'     => 'plugin',
        'settings' => ['blog' => '#params.logNotices'],

        'details'    => 'https://open-time.net/?q=logNotices',
        'support'    => 'https://github.com/franck-paul/logNotices',
        'repository' => 'https://raw.githubusercontent.com/franck-paul/logNotices/master/dcstore.xml',
    ]
);
