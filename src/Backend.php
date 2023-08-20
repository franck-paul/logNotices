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

use dcCore;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Core\Backend\Menus;
use Dotclear\Core\Process;

class Backend extends Process
{
    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('Store notices in database') . __('Store all or error only notices in the database');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (My::checkContext(My::MANAGE)) {
            // Register menu
            dcCore::app()->admin->menus[Menus::MENU_SYSTEM]->addItem(
                __('Notices'),
                My::manageUrl(),
                My::icons(),
                preg_match(My::urlScheme(), $_SERVER['REQUEST_URI']),
                My::checkContext(My::MENU)
            );

            /* Register favorite */
            dcCore::app()->addBehavior('adminDashboardFavoritesV2', function (Favorites $favs) {
                $favs->register('logNotices', [
                    'title'      => __('Notices'),
                    'url'        => My::manageUrl(),
                    'small-icon' => My::icons(),
                    'large-icon' => My::icons(),
                ]);
            });

            dcCore::app()->addBehaviors([
                // Settings behaviors
                'adminBlogPreferencesFormV2'    => BackendBehaviors::adminBlogPreferencesForm(...),
                'adminBeforeBlogSettingsUpdate' => BackendBehaviors::adminBeforeBlogSettingsUpdate(...),
            ]);
        }

        // Store error and standard DC notices in the database
        dcCore::app()->addBehaviors([
            'adminPageNotificationError' => BackendBehaviors::adminPageNotificationError(...),
            'adminPageNotification'      => BackendBehaviors::adminPageNotification(...),
        ]);

        return true;
    }
}
