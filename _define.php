<?php
# -- BEGIN LICENSE BLOCK ----------------------------------
# This file is part of notifyMe, a plugin for Dotclear 2.
#
# Copyright (c) Franck Paul and contributors
#
# Licensed under the GPL version 2.0 license.
# A copy of this license is available in LICENSE file or at
# http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
# -- END LICENSE BLOCK ------------------------------------

if (!defined('DC_RC_PATH')) { return; }

$this->registerModule(
	/* Name */				"Store notices in log table",
	/* Description*/		"Store all or error only notices in the database",
	/* Author */			"Franck Paul and contributors",
	/* Version */			'0.1',
	array(
		/* Dependencies */	'requires' =>		array(array('core','2.11')),
		/* Permissions */	'permissions' =>	'usage',
		/* Type */			'type' =>			'plugin',
		/* Settings */		'settings' => 		array('blog' => '#params.logNotices')
	)
);
