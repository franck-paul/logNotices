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
if (!defined('DC_RC_PATH')) { return; }

class dcLogNoticesActionsPage extends dcActionsPage
{
	public function __construct($core,$uri,$redirect_args = array())
	{
		parent::__construct($core,$uri,$redirect_args);
		$this->redirect_fields = array();
		$this->caller_title = __('Notices');
	}

	public function error(Exception $e)
	{
		$this->core->error->add($e->getMessage());
		$this->beginPage(dcPage::breadcrumb(
			array(
				html::escapeHTML($this->core->blog->name) => '',
				__('Notices') => $this->getRedirection(true),
				__('Notices actions') => ''
			))
		);
		$this->endPage();
	}

	public function beginPage($breadcrumb = '',$head = '')
	{
		echo '<html><head><title>'.__('Notices').'</title>'.
			$head.
			'</script></head><body>'.
			$breadcrumb;
		echo '<p><a class="back" href="'.$this->getRedirection(true).'">'.__('Back to notices list').'</a></p>';
	}

	public function endPage()
	{
		echo '</body></html>';
	}

	protected function fetchEntries($from)
	{
		$params = array(
			'log_table' => array('dc-sys-error','dc-success','dc-warning','dc-error','dc-notice')
		);
		if (!empty($from['entries']))
		{
			$entries = $from['entries'];
			foreach ($entries as $k => $v) {
				$entries[$k] = (integer) $v;
			}

			$params['sql'] = 'AND L.log_id IN('.implode(',',$entries).') ';
		} else {
			$params['sql'] = 'AND 1=0 ';
		}

		$lines = $this->core->log->getLogs($params);
		$this->rs = $lines;
	}
}
