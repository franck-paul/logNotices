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
class dcLogNoticesActions extends dcActions
{
    public function __construct($uri, $redirect_args = [])
    {
        parent::__construct($uri, $redirect_args);
        $this->redirect_fields = [];
        $this->caller_title    = __('Notices');
    }

    public function error(Exception $e)
    {
        dcCore::app()->error->add($e->getMessage());
        $this->beginPage(
            dcPage::breadcrumb(
                [
                    html::escapeHTML(dcCore::app()->blog->name) => '',
                    __('Notices')                               => $this->getRedirection(true),
                    __('Notices actions')                       => '',
                ]
            )
        );
        $this->endPage();
    }

    public function beginPage($breadcrumb = '', $head = '')
    {
        echo '<html><head><title>' . __('Notices') . '</title>' .
            $head .
            '</script></head><body>' .
            $breadcrumb;
        echo '<p><a class="back" href="' . $this->getRedirection(true) . '">' . __('Back to notices list') . '</a></p>';
    }

    public function endPage()
    {
        echo '</body></html>';
    }

    protected function fetchEntries($from)
    {
        $params = [
            'log_table' => ['dc-sys-error', 'dc-success', 'dc-warning', 'dc-error', 'dc-notice'],
        ];
        if (!empty($from['entries'])) {
            $entries = $from['entries'];
            foreach ($entries as $k => $v) {
                $entries[$k] = (int) $v;
            }

            $params['sql'] = 'AND L.log_id IN(' . implode(',', $entries) . ') ';
        } else {
            $params['sql'] = 'AND 1=0 ';
        }

        $lines    = dcCore::app()->log->getLogs($params);
        $this->rs = $lines;
    }
}
