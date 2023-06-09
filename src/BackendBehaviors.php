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
use dcError;
use dcLog;
use dcNamespace;
use Exception;
use form;

class BackendBehaviors
{
    public static function adminBlogPreferencesForm()
    {
        $settings = dcCore::app()->blog->settings->get(My::id());
        echo
        '<div class="fieldset" id="logNotices"><h4>' . __('Notices') . '</h4>' .
        '<p><label class="classic">' .
        form::checkbox('lognotices_active', '1', $settings->active) .
        __('Store notices in database') . '</label></p>' .
        '<h5>' . __('Options') . '</h5>' .
        '<p><label for="lognotices_error_only" class="classic">' .
        form::checkbox('lognotices_error_only', '1', $settings->error_only) .
        __('Only error notices') . '</label>' . '</p>' .
            '</div>';
    }

    public static function adminBeforeBlogSettingsUpdate()
    {
        $settings = dcCore::app()->blog->settings->get(My::id());
        $settings->put('active', !empty($_POST['lognotices_active']), dcNamespace::NS_BOOL);
        $settings->put('error_only', !empty($_POST['lognotices_error_only']), dcNamespace::NS_BOOL);
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
        $settings = dcCore::app()->blog->settings->get(My::id());
        if ($settings->active) {
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
        $settings = dcCore::app()->blog->settings->get(My::id());
        if ($settings->active && !$settings->error_only) {
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
