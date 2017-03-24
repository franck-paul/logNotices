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
$rs = $core->log->getLogs(array('log_table' => array('dc-sys-error','dc-success','dc-warning','dc-error','dc-notice')));

?>
<html>
<head>
	<title><?php echo __('Notices'); ?></title>
</head>

<body>
<?php
echo dcPage::breadcrumb(
	array(
		html::escapeHTML($core->blog->name) => '',
		__('Notifications in database') => ''
	));

if (!empty($msg)) dcPage::success($msg);
?>

	<form method="post" action="plugin.php">
<?php
		// Display list of stored notices
		if ($rs->count()) {

			while ($rs->fetch()) {
				echo '<p>'.
					$rs->log_id.' '.
					$rs->user_id.' '.
					$rs->blog_id.' '.
					$rs->log_table.' '.
					dt::str(__('%Y/%m/%d %H:%M:%S'),strtotime($rs->log_dt),$core->auth->getInfo('user_tz')).' '.
					$rs->log_ip.' '.
					html::escapeHTML($rs->log_msg).
					'</p>';
			}
		} else {
			echo '<p>'.__('No dotclear notices stored in database').'</p>';
		}
?>
		<p>
			<input type="hidden" name="p" value="logNotices" /><?php echo $core->formNonce(); ?>
		</p>
	</form>
</body>
</html>
