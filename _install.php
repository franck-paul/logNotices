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

$new_version = $core->plugins->moduleInfo('logNotices', 'version');
$old_version = $core->getVersion('logNotices');

if (version_compare($old_version, $new_version, '>=')) {
    return;
}

try
{
    $core->blog->settings->addNamespace('logNotices');

    $core->blog->settings->logNotices->put('active', false, 'boolean', 'Active', false, true);
    $core->blog->settings->logNotices->put('error_only', false, 'boolean', 'Only error notices?', false, true);

    $core->setVersion('logNotices', $new_version);

    return true;
} catch (Exception $e) {
    $core->error->add($e->getMessage());
}
return false;
