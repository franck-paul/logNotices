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

use Dotclear\App;
use Dotclear\Core\Backend\Favorites;
use Dotclear\Helper\Process\TraitProcess;

class Backend
{
    use TraitProcess;

    public static function init(): bool
    {
        // dead but useful code, in order to have translations
        __('Store notices in database');
        __('Store all or error only notices in the database');

        return self::status(My::checkContext(My::BACKEND));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        if (My::checkContext(My::MANAGE)) {
            // Register menu
            My::addBackendMenuItem(App::backend()->menus()::MENU_SYSTEM);

            /* Register favorite */
            App::behavior()->addBehavior('adminDashboardFavoritesV2', static function (Favorites $favs): string {
                $favs->register('logNotices', [
                    'title'      => __('Notices'),
                    'url'        => My::manageUrl(),
                    'small-icon' => My::icons(),
                    'large-icon' => My::icons(),
                ]);

                return '';
            });

            App::behavior()->addBehaviors([
                // Settings behaviors
                'adminBlogPreferencesFormV2'    => BackendBehaviors::adminBlogPreferencesForm(...),
                'adminBeforeBlogSettingsUpdate' => BackendBehaviors::adminBeforeBlogSettingsUpdate(...),
            ]);
        }

        // Store error and standard DC notices in the database
        App::behavior()->addBehaviors([
            'adminPageNotificationError' => BackendBehaviors::adminPageNotificationError(...),
            'adminPageNotification'      => BackendBehaviors::adminPageNotification(...),
        ]);

        return true;
    }
}
