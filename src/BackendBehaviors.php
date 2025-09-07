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
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Fieldset;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Legend;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Text;
use Dotclear\Interface\Core\ErrorInterface;

class BackendBehaviors
{
    public static function adminBlogPreferencesForm(): string
    {
        echo (new Fieldset('logNotices'))
            ->legend(new Legend(__('Notices')))
            ->fields([
                (new Para())
                    ->items([
                        (new Checkbox('lognotices_active', My::settings()->active))
                            ->value(1)
                            ->label(new Label(__('Store notices in database'), Label::IL_FT)),
                    ]),
                (new Text('h5', __('Options'))),
                (new Para())
                    ->items([
                        (new Checkbox('lognotices_error_only', My::settings()->error_only))
                            ->value(1)
                            ->label(new Label(__('Only error notices'), Label::IL_FT)),
                    ]),
            ])
        ->render();

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
        $cur = App::db()->con()->openCursor(App::db()->con()->prefix() . App::log()::LOG_TABLE_NAME);

        $cur->user_id   = App::auth()->userID();
        $cur->log_msg   = $message;
        $cur->log_table = $table;

        App::log()->addLog($cur);
    }

    /**
     * @param      mixed            $unused  The unused
     * @param      ErrorInterface   $err     The error
     */
    public static function adminPageNotificationError($unused, ErrorInterface $err): string
    {
        $settings = My::settings();
        if ($settings->active && $err->flag()) {
            $message = '';
            foreach ($err->dump() as $msg) {
                $message .= ($message === '' ? '' : ' – ') . $msg;
            }

            self::addLogNotice('dc-sys-error', $message);
        }

        return '';
    }

    /**
     * @param      mixed                    $unused  The unused
     * @param      array<string, string>    $notice  The notice
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
