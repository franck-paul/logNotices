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

if (!defined('DC_CONTEXT_ADMIN')) {return;}

// dead but useful code, in order to have translations
__('Store notices in database') . __('Store all or error only notices in the database');

if ($core->auth->isSuperAdmin()) {
    // Register menu
    $_menu['System']->addItem(__('Notices'),
        $core->adminurl->get('admin.plugin.logNotices'),
        urldecode(dcPage::getPF('logNotices/icon.png')),
        preg_match('/' . preg_quote($core->adminurl->get('admin.plugin.logNotices')) . '(&.*)/', $_SERVER['REQUEST_URI']),
        $core->auth->isSuperAdmin());

    // Register favorite
    $core->addBehavior('adminDashboardFavorites', ['logNoticesBehaviors', 'adminDashboardFavorites']);

    // Settings behaviors
    $core->addBehavior('adminBlogPreferencesForm', ['logNoticesBehaviors', 'adminBlogPreferencesForm']);
    $core->addBehavior('adminBeforeBlogSettingsUpdate', ['logNoticesBehaviors', 'adminBeforeBlogSettingsUpdate']);
}

// Store error and standard DC notices in the database
$core->addBehavior('adminPageNotificationError', ['logNoticesBehaviors', 'adminPageNotificationError']);
$core->addBehavior('adminPageNotification', ['logNoticesBehaviors', 'adminPageNotification']);

class logNoticesBehaviors
{
    public static function adminDashboardFavorites($core, $favs)
    {
        $favs->register('logNotices', [
            'title'       => __('Notices'),
            'url'         => $core->adminurl->get('admin.plugin.logNotices'),
            'small-icon'  => urldecode(dcPage::getPF('logNotices/icon.png')),
            'large-icon'  => urldecode(dcPage::getPF('logNotices/icon-big.png')),
            'permissions' => $core->auth->isSuperAdmin()
        ]);
    }

    public static function adminBlogPreferencesForm($core, $settings)
    {
        $settings->addNameSpace('logNotices');
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
        $settings->addNameSpace('logNotices');
        $settings->logNotices->put('active', !empty($_POST['lognotices_active']), 'boolean');
        $settings->logNotices->put('error_only', !empty($_POST['lognotices_error_only']), 'boolean');
    }

    private static function addLogNotice($core, $table, $message)
    {
        // Add new log
        $cur = $core->con->openCursor($core->prefix . 'log');

        $cur->user_id   = $core->auth->userID();
        $cur->log_msg   = $message;
        $cur->log_table = $table;

        $core->log->addLog($cur);
    }

    public static function adminPageNotificationError($core, $err)
    {
        $core->blog->settings->addNamespace('logNotices');
        if ($core->blog->settings->logNotices->active) {
            $msg = (string) $err;
            self::addLogNotice($core, 'dc-sys-error', $msg);
        }
    }

    public static function adminPageNotification($core, $notice)
    {
        $core->blog->settings->addNamespace('logNotices');
        if ($core->blog->settings->logNotices->active && !$core->blog->settings->logNotices->error_only) {
            $type = [
                'success' => 'dc-success',
                'warning' => 'dc-warning',
                'error'   => 'dc-error'];

            $table = isset($type[$notice['class']]) ? $type[$notice['class']] : 'dc-notice';
            $msg   = $notice['text'];
            if (!isset($notice['with_ts']) || ($notice['with_ts'] == true)) {
                $msg  = $notice['ts'] . ' ' . $msg;
            }
            self::addLogNotice($core, $table, $msg);
        }
    }
}
