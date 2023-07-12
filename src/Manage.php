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
use dcNsProcess;
use dcPage;
use Dotclear\Helper\Html\Html;
use Exception;
use form;

class Manage extends dcNsProcess
{
    protected static $init = false; /** @deprecated since 2.27 */
    /**
     * Initializes the page.
     */
    public static function init(): bool
    {
        static::$init = My::checkContext(My::MANAGE);

        return static::$init;
    }

    /**
     * Processes the request(s).
     */
    public static function process(): bool
    {
        if (!static::$init) {
            return false;
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
            $lines    = dcCore::app()->log->getLogs($params);
            $counter  = dcCore::app()->log->getLogs($params, true);
            $log_list = new BackendList($lines, $counter->f(0));
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        // Cope with actions
        $log_actions = new BackendActions(dcCore::app()->admin->url->get('admin.plugin.' . My::id()));
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
        if (!static::$init) {
            return;
        }

        $head = dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
        dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js') .
        dcPage::jsJson('lognotices', [
            'confirm_delete_notices' => __('Are you sure you want to delete selected notices?'),
        ]);

        dcPage::openModule(__('Notices'), $head);

        echo dcPage::breadcrumb(
            [
                Html::escapeHTML(dcCore::app()->blog->name) => '',
                __('Notifications in database')             => '',
            ]
        );
        echo dcPage::notices();

        if (!empty($_GET['del'])) {
            dcPage::success(__('Selected notices have been successfully deleted.'));
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
            $lines    = dcCore::app()->log->getLogs($params);
            $counter  = dcCore::app()->log->getLogs($params, true);
            $log_list = new BackendList($lines, $counter->f(0));
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        $log_actions = new BackendActions(dcCore::app()->adminurl->get('admin.plugin.' . My::id()));

        $log_list->display(     // @phpstan-ignore-line
            $page,
            $nb_per_page,
            '<form action="' . dcCore::app()->adminurl->get('admin.plugin') . '" method="post" id="form-notices">' .

            '%s' .

            '<div class="two-cols">' .
            '<p class="col checkboxes-helpers"></p>' .

            '<p class="col right"><label for="action" class="classic">' . __('Selected notices action:') . '</label> ' .
            form::combo('action', $log_actions->getCombo()) .
            '<input id="do-action" type="submit" value="' . __('ok') . '" />' .
            form::hidden(['p'], 'pages') .
            form::hidden(['act'], 'list') .
            dcCore::app()->formNonce() .
            '</p></div>' .
            '</form>'
        );

        dcPage::closeModule();
    }
}
