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
use Dotclear\Core\Backend\Notices;
use Dotclear\Core\Backend\Page;
use Dotclear\Core\Process;
use Dotclear\Helper\Html\Html;
use Exception;
use form;

class Manage extends Process
{
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        return self::status(My::checkContext(My::MANAGE));
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        // Cope with actions
        $log_actions = new BackendActions(App::backend()->url()->get('admin.plugin.' . My::id()));
        if ($log_actions->process()) {
            return true;
        }

        return true;
    }

    /**
     * Renders the page.
     */
    public static function render(): void
    {
        if (!self::status()) {
            return;
        }

        $head = Page::jsLoad('js/jquery/jquery-ui.custom.js') .
        Page::jsLoad('js/jquery/jquery.ui.touch-punch.js') .
        Page::jsJson('lognotices', [
            'confirm_delete_notices' => __('Are you sure you want to delete selected notices?'),
        ]);

        Page::openModule(My::name(), $head);

        echo Page::breadcrumb(
            [
                Html::escapeHTML(App::blog()->name()) => '',
                __('Notifications in database')       => '',
            ]
        );
        echo Notices::getNotices();

        if (!empty($_GET['del'])) {
            Notices::success(__('Selected notices have been successfully deleted.'));
        }

        // Get current list of stored notices
        $params = [
            'log_table' => ['dc-sys-error', 'dc-success', 'dc-warning', 'dc-error', 'dc-notice'],
        ];

        $page        = !empty($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
        $nb_per_page = 30;

        if (!empty($_GET['nb']) && (int) $_GET['nb'] > 0) {
            $nb_per_page = (int) $_GET['nb'];
        }

        $params['limit'] = [(($page - 1) * $nb_per_page), $nb_per_page];
        $params['order'] = 'log_dt DESC';

        try {
            $lines    = App::log()->getLogs($params);
            $counter  = App::log()->getLogs($params, true);
            $log_list = new BackendList($lines, $counter->f(0));

            $log_actions = new BackendActions(App::backend()->url()->get('admin.plugin.' . My::id()));

            $log_list->display(
                $page,
                $nb_per_page,
                '<form action="' . App::backend()->url()->get('admin.plugin') . '" method="post" id="form-notices">' .

                '%s' .

                '<div class="two-cols">' .
                '<p class="col checkboxes-helpers"></p>' .

                '<p class="col right"><label for="action" class="classic">' . __('Selected notices action:') . '</label> ' .
                form::combo('action', $log_actions->getCombo()) .
                '<input id="do-action" type="submit" value="' . __('ok') . '" />' .
                My::parsedHiddenFields([
                    'p'   => 'pages',
                    'act' => 'list',
                ]) .
                '</p></div>' .
                '</form>'
            );
        } catch (Exception $e) {
            App::error()->add($e->getMessage());
        }

        Page::closeModule();
    }
}
