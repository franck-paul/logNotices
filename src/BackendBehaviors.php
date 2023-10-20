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
use Dotclear\Interface\Core\ErrorInterface;
use Dotclear\Interface\Core\LogInterface;
use form;

class BackendBehaviors
{
    public static function adminBlogPreferencesForm(): string
    {
        $settings = My::settings();
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

        return '';
    }

    public static function adminBeforeBlogSettingsUpdate(): string
    {
        $settings = My::settings();
        $settings->put('active', !empty($_POST['lognotices_active']), App::blogWorkspace()::NS_BOOL);
        $settings->put('error_only', !empty($_POST['lognotices_error_only']), App::blogWorkspace()::NS_BOOL);

        return '';
    }

    private static function addLogNotice(string $table, string $message): void
    {
        // Add new log
        $cur = App::con()->openCursor(App::con()->prefix() . LogInterface::LOG_TABLE_NAME);

        $cur->user_id   = App::auth()->userID();
        $cur->log_msg   = $message;
        $cur->log_table = $table;

        App::log()->addLog($cur);
    }

    /**
     * @param      mixed            $unused  The unused
     * @param      ErrorInterface   $err     The error
     *
     * @return     string
     */
    public static function adminPageNotificationError($unused, ErrorInterface $err): string
    {
        $settings = My::settings();
        if ($settings->active) {
            if ($err->flag()) {
                $message = '';
                foreach ($err->dump() as $msg) {
                    $message .= ($message === '' ? '' : ' – ') . $msg;
                }

                self::addLogNotice('dc-sys-error', $message);
            }
        }

        return '';
    }

    /**
     * @param      mixed                    $unused  The unused
     * @param      array<string, string>    $notice  The notice
     *
     * @return     string
     */
    public static function adminPageNotification($unused, array $notice): string
    {
        $settings = My::settings();
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
            self::addLogNotice($table, $msg);
        }

        return '';
    }
}
