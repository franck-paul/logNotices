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

use ArrayObject;
use Dotclear\App;
use Dotclear\Core\Backend\Action\Actions;
use Dotclear\Core\Backend\Page;
use Dotclear\Helper\Html\Html;
use Exception;

/**
 * @todo switch Helper/Html/Form/...
 */
class BackendActions extends Actions
{
    /**
     * Constructs a new instance.
     *
     * @param      null|string              $uri            The uri
     * @param      array<string, string>    $redirect_args  The redirect arguments
     */
    public function __construct(?string $uri, array $redirect_args = [])
    {
        parent::__construct($uri, $redirect_args);
        $this->redirect_fields = [];
        $this->caller_title    = __('Notices');
    }

    public function error(Exception $e): void
    {
        App::error()->add($e->getMessage());
        $this->beginPage(
            Page::breadcrumb(
                [
                    Html::escapeHTML(App::blog()->name()) => '',
                    __('Notices')                         => $this->getRedirection(true),
                    __('Notices actions')                 => '',
                ]
            )
        );
        $this->endPage();
    }

    public function beginPage(string $breadcrumb = '', string $head = ''): void
    {
        echo '<html><head><title>' . __('Notices') . '</title>' .
            $head .
            '</script></head><body>' .
            $breadcrumb;
        echo '<p><a class="back" href="' . $this->getRedirection(true) . '">' . __('Back to notices list') . '</a></p>';
    }

    public function endPage(): void
    {
        echo '</body></html>';
    }

    /**
     * Fetches entries.
     *
     * @param      ArrayObject<int|string, mixed>  $from   The from
     */
    protected function fetchEntries(ArrayObject $from): void
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

        $lines    = App::log()->getLogs($params);
        $this->rs = $lines;
    }
}
