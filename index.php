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
dcPage::checkSuper();

// Get current list of stored notices
$params = [
    'log_table' => ['dc-sys-error', 'dc-success', 'dc-warning', 'dc-error', 'dc-notice']
];

$page        = !empty($_GET['page']) ? max(1, (integer) $_GET['page']) : 1;
$nb_per_page = 30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
    $nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = [(($page - 1) * $nb_per_page), $nb_per_page];
$params['order'] = 'log_dt DESC';

try {
    $lines    = $core->log->getLogs($params);
    $counter  = $core->log->getLogs($params, true);
    $log_list = new adminLogNoticesList($core, $lines, $counter->f(0));
} catch (Exception $e) {
    $core->error->add($e->getMessage());
}

// Cope with actions
$log_actions = new dcLogNoticesActionsPage($core, 'plugin.php', ['p' => 'logNotices']);
if ($log_actions->process()) {
    return;
}

// View log notices
?>
<html>
<head>
  <title><?php echo __('Notices'); ?></title>
<?php
echo
dcPage::jsLoad('js/jquery/jquery-ui.custom.js') .
dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js') .
dcPage::jsJson('lognotices', [
    'confirm_delete_notices' => __("Are you sure you want to delete selected notices?")
]) .
dcPage::jsLoad(dcPage::getPF('logNotices/list.js'));
?>
</head>

<body>
<?php
echo dcPage::breadcrumb(
    [
        html::escapeHTML($core->blog->name) => '',
        __('Notifications in database')     => ''
    ]);

if (!empty($msg)) {
    dcPage::success($msg);
}

if (!empty($_GET['del'])) {
    dcPage::success(__('Selected notices have been successfully deleted.'));
}
if (!$core->error->flag()) {
    $log_list->display($page, $nb_per_page,
        '<form action="' . $core->adminurl->get('admin.plugin') . '" method="post" id="form-notices">' .

        '%s' .

        '<div class="two-cols">' .
        '<p class="col checkboxes-helpers"></p>' .

        '<p class="col right"><label for="action" class="classic">' . __('Selected notices action:') . '</label> ' .
        form::combo('action', $log_actions->getCombo()) .
        '<input id="do-action" type="submit" value="' . __('ok') . '" />' .
        form::hidden(['p'], 'pages') .
        form::hidden(['act'], 'list') .
        $core->formNonce() .
        '</p></div>' .
        '</form>');
}
?>
</body>
</html>
