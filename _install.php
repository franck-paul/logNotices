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

if (!defined('DC_CONTEXT_ADMIN')) { return; }

$new_version = $core->plugins->moduleInfo('logNotices','version');
$old_version = $core->getVersion('logNotices');

if (version_compare($old_version,$new_version,'>=')) return;

try
{
	$core->blog->settings->addNamespace('logNotices');

	$core->blog->settings->logNotices->put('active',false,'boolean','Active',false,true);
	$core->blog->settings->logNotices->put('error_only',false,'boolean','Only error notices?',false,true);

	$core->setVersion('logNotices',$new_version);

	return true;
}
catch (Exception $e)
{
	$core->error->add($e->getMessage());
}
return false;
