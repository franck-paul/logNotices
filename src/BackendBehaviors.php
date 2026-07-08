<?php

/**
 * @brief logNotices, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul contact@open-time.net
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\logNotices;

use Dotclear\App;
use Dotclear\Core\Backend\Notice;
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
                        (new Checkbox('lognotices_active', My::settings()->getBool('active', false)))
                            ->value(1)
                            ->label(new Label(__('Store notices in database'), Label::IL_FT)),
                    ]),
                (new Text('h5', __('Options'))),
                (new Para())
                    ->items([
                        (new Checkbox('lognotices_error_only', My::settings()->getBool('error_only', false)))
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
        if ($settings->getBool('active') && $err->flag()) {
            $message = '';
            foreach ($err->dump() as $msg) {
                $message .= ($message === '' ? '' : ' – ') . $msg;
            }

            self::addLogNotice('dc-sys-error', $message);
        }

        return '';
    }

    public static function adminPageNotification(Notice $notice): string
    {
        $settings = My::settings();
        if ($settings->getBool('active') && !$settings->getBool('error_only')) {
            $type = [
                'success' => 'dc-success',
                'warning' => 'dc-warning',
                'error'   => 'dc-error', ];

            $class = $notice->getClass();
            $table = $class !== '' && isset($type[$class]) ? $type[$class] : 'dc-notice';

            $msg = $notice->getMsg();
            if ($notice->useTs() && $notice->getTs() !== '') {
                $msg = $notice->getTs() . ' ' . $msg;
            }

            self::addLogNotice($table, $msg);
        }

        return '';
    }
}
