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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

// dead but useful code, in order to have translations
__('Store notices in database') . __('Store all or error only notices in the database');

class logNoticesBehaviors
{
    public static function adminDashboardFavorites($favs)
    {
        $favs->register('logNotices', [
            'title'      => __('Notices'),
            'url'        => dcCore::app()->adminurl->get('admin.plugin.logNotices'),
            'small-icon' => urldecode(dcPage::getPF('logNotices/icon.png')),
            'large-icon' => urldecode(dcPage::getPF('logNotices/icon-big.png')),
        ]);
    }

    public static function adminBlogPreferencesForm($settings)
    {
        echo
        '<div class="fieldset" id="logNotices"><h4>' . __('Notices') . '</h4>' .
        '<p><label class="classic">' .
        form::checkbox('lognotices_active', '1', $settings->logNotices->active) .
        __('Store notices in database') . '</label></p>' .
        '<h5>' . __('Options') . '</h5>' .
        '<p><label for="lognotices_error_only" class="classic">' .
        form::checkbox('lognotices_error_only', '1', $settings->logNotices->error_only) .
        __('Only error notices') . '</label>' . '</p>' .
            '</div>';
    }

    public static function adminBeforeBlogSettingsUpdate($settings)
    {
        $settings->logNotices->put('active', !empty($_POST['lognotices_active']), 'boolean');
        $settings->logNotices->put('error_only', !empty($_POST['lognotices_error_only']), 'boolean');
    }

    private static function addLogNotice($core, $table, $message)
    {
        // Add new log
        $cur = dcCore::app()->con->openCursor(dcCore::app()->prefix . dcLog::LOG_TABLE_NAME);

        $cur->user_id   = dcCore::app()->auth->userID();
        $cur->log_msg   = $message;
        $cur->log_table = $table;

        dcCore::app()->log->addLog($cur);
    }

    public static function adminPageNotificationError($core, $err)
    {
        if (dcCore::app()->blog->settings->logNotices->active) {
            if ($err instanceof Exception) {
                $msg = $err->getMessage();
            } elseif ($err instanceof dcError) {
                $msg = $err->toHTML();
            } else {
                $msg = (string) $err;
            }
            self::addLogNotice(dcCore::app(), 'dc-sys-error', $msg);
        }
    }

    public static function adminPageNotification($core, $notice)
    {
        if (dcCore::app()->blog->settings->logNotices->active && !dcCore::app()->blog->settings->logNotices->error_only) {
            $type = [
                'success' => 'dc-success',
                'warning' => 'dc-warning',
                'error'   => 'dc-error', ];

            $table = $type[$notice['class']] ?? 'dc-notice';
            $msg   = $notice['text'];
            if (!isset($notice['with_ts']) || $notice['with_ts']) {
                $msg = $notice['ts'] . ' ' . $msg;
            }
            self::addLogNotice(dcCore::app(), $table, $msg);
        }
    }
}

if (dcCore::app()->auth->isSuperAdmin()) {
    // Register menu
    dcCore::app()->menu[dcAdmin::MENU_SYSTEM]->addItem(
        __('Notices'),
        dcCore::app()->adminurl->get('admin.plugin.logNotices'),
        urldecode(dcPage::getPF('logNotices/icon.png')),
        preg_match('/' . preg_quote(dcCore::app()->adminurl->get('admin.plugin.logNotices')) . '(&.*)/', $_SERVER['REQUEST_URI']),
        dcCore::app()->auth->isSuperAdmin()
    );

    dcCore::app()->addBehaviors([
        // Register favorite
        'adminDashboardFavoritesV2'     => [logNoticesBehaviors::class, 'adminDashboardFavorites'],

        // Settings behaviors
        'adminBlogPreferencesFormV2'    => [logNoticesBehaviors::class, 'adminBlogPreferencesForm'],
        'adminBeforeBlogSettingsUpdate' => [logNoticesBehaviors::class, 'adminBeforeBlogSettingsUpdate'],
    ]);
}

// Store error and standard DC notices in the database
dcCore::app()->addBehaviors([
    'adminPageNotificationError' => [logNoticesBehaviors::class, 'adminPageNotificationError'],
    'adminPageNotification'      => [logNoticesBehaviors::class, 'adminPageNotification'],
]);
