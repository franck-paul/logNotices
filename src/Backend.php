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
declare(strict_types=1);

namespace Dotclear\Plugin\logNotices;

use dcAdmin;
use dcCore;
use dcNsProcess;
use Dotclear\Core\Backend\Favorites;

class Backend extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::BACKEND);

        // dead but useful code, in order to have translations
        __('Store notices in database') . __('Store all or error only notices in the database');

        return static::$init;
    }

    public static function process(): bool
    {
        if (!static::$init) {
            return false;
        }

        if (My::checkContext(My::MANAGE)) {
            // Register menu
            dcCore::app()->menu[dcAdmin::MENU_SYSTEM]->addItem(
                __('Notices'),
                My::makeUrl(),
                My::icons(),
                preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
                My::checkContext(My::MENU)
            );

            /* Register favorite */
            dcCore::app()->addBehavior('adminDashboardFavoritesV2', function (Favorites $favs) {
                $favs->register('logNotices', [
                    'title'      => __('Notices'),
                    'url'        => My::makeUrl(),
                    'small-icon' => My::icons(),
                    'large-icon' => My::icons(),
                ]);
            });

            dcCore::app()->addBehaviors([
                // Settings behaviors
                'adminBlogPreferencesFormV2'    => [BackendBehaviors::class, 'adminBlogPreferencesForm'],
                'adminBeforeBlogSettingsUpdate' => [BackendBehaviors::class, 'adminBeforeBlogSettingsUpdate'],
            ]);
        }

        // Store error and standard DC notices in the database
        dcCore::app()->addBehaviors([
            'adminPageNotificationError' => [BackendBehaviors::class, 'adminPageNotificationError'],
            'adminPageNotification'      => [BackendBehaviors::class, 'adminPageNotification'],
        ]);

        return true;
    }
}
