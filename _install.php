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
if (!defined('DC_CONTEXT_ADMIN')) {
    return;
}

$new_version = dcCore::app()->plugins->moduleInfo('logNotices', 'version');
$old_version = dcCore::app()->getVersion('logNotices');

if (version_compare($old_version, $new_version, '>=')) {
    return;
}

try {
    dcCore::app()->blog->settings->addNamespace('logNotices');

    dcCore::app()->blog->settings->logNotices->put('active', false, 'boolean', 'Active', false, true);
    dcCore::app()->blog->settings->logNotices->put('error_only', false, 'boolean', 'Only error notices?', false, true);

    dcCore::app()->setVersion('logNotices', $new_version);

    return true;
} catch (Exception $e) {
    dcCore::app()->error->add($e->getMessage());
}

return false;
