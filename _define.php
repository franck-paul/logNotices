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
if (!defined('DC_RC_PATH')) {
    return;
}

$this->registerModule(
    'Store notices in log table',                      // Name
    'Store all or error only notices in the database', // Description
    'Franck Paul and contributors',                    // Author
    '0.2',
    [
        'requires'    => [['core', '2.23']],               // Dependencies
        'permissions' => 'usage,contentadmin',             // Permissions
        'type'        => 'plugin',                         // Type
        'settings'    => ['blog' => '#params.logNotices'], // Settings

        'details'    => 'https://open-time.net/?q=logNotices',       // Details URL
        'support'    => 'https://github.com/franck-paul/logNotices', // Support URL
        'repository' => 'https://raw.githubusercontent.com/franck-paul/logNotices/master/dcstore.xml',
    ]
);
