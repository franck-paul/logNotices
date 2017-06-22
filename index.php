<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of logNotices, a plugin for Dotclear 2.
#
# Copyright (c) Franck Paul and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_CONTEXT_ADMIN')) { exit; }
dcPage::checkSuper();

// Get current list of stored notices
$params = array(
	'log_table' => array('dc-sys-error','dc-success','dc-warning','dc-error','dc-notice')
);

$page = !empty($_GET['page']) ? max(1,(integer) $_GET['page']) : 1;
$nb_per_page =  30;

if (!empty($_GET['nb']) && (integer) $_GET['nb'] > 0) {
	$nb_per_page = (integer) $_GET['nb'];
}

$params['limit'] = array((($page - 1) * $nb_per_page),$nb_per_page);
$params['order'] = 'log_dt DESC';

try {
	$lines = $core->log->getLogs($params);
	$counter = $core->log->getLogs($params,true);
	$log_list = new adminLogNoticesList($core,$lines,$counter->f(0));
} catch (Exception $e) {
	$core->error->add($e->getMessage());
}

// Cope with actions
$log_actions = new dcLogNoticesActionsPage($core,'plugin.php',array('p' => 'logNotices'));
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
	dcPage::jsLoad('js/jquery/jquery-ui.custom.js').
	dcPage::jsLoad('js/jquery/jquery.ui.touch-punch.js').
	dcPage::jsLoad(dcPage::getPF('logNotices/list.js')).
	'<script type="text/javascript">'."\n".
		dcPage::jsVar('dotclear.msg.confirm_delete_notices',__("Are you sure you want to delete selected notices?")).
	'</script>';
?>
</head>

<body>
<?php
echo dcPage::breadcrumb(
	array(
		html::escapeHTML($core->blog->name) => '',
		__('Notifications in database') => ''
	));

if (!empty($msg)) dcPage::success($msg);
if (!empty($_GET['del'])) {
	dcPage::success(__('Selected notices have been successfully deleted.'));
}
if (!$core->error->flag())
{
	$log_list->display($page,$nb_per_page,
	'<form action="'.$core->adminurl->get('admin.plugin').'" method="post" id="form-notices">'.

	'%s'.

	'<div class="two-cols">'.
	'<p class="col checkboxes-helpers"></p>'.

	'<p class="col right"><label for="action" class="classic">'.__('Selected notices action:').'</label> '.
	form::combo('action',$log_actions->getCombo()).
	'<input id="do-action" type="submit" value="'.__('ok').'" />'.
	form::hidden(array('p'),'pages').
	form::hidden(array('act'),'list').
	$core->formNonce().
	'</p></div>'.
	'</form>');
}
?>
</body>
</html>
