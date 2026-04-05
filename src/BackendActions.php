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
use Dotclear\Helper\Html\Form\Link;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Html;
use Exception;

class BackendActions extends Actions
{
    /**
     * Constructs a new instance.
     *
     * @param      null|string              $uri            The uri
     * @param      array<string, string>    $redirect_args  The redirect arguments
     *
     * @todo        Add deletion action
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
            App::backend()->page()->breadcrumb(
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
        App::backend()->page()->openModule(
            __('Notices'),
            $head
        );
        echo
        $breadcrumb;

        echo (new Para())
            ->items([
                (new Link())
                    ->class('back')
                    ->href($this->getRedirection(true))
                    ->text(__('Back to notices list')),
            ])
        ->render();
    }

    public function endPage(): void
    {
        App::backend()->page()->closeModule();
    }

    /**
     * Fetches entries.
     *
     * @param      ArrayObject<int|string, mixed>  $from   The from
     */
    protected function fetchEntries(ArrayObject $from): void
    {
        $params = [];
        if (!empty($from['entries'])) {
            $entries = isset($from['entries']) && is_array($entries = $from['entries']) ? $entries : [];
            $list    = [];
            foreach ($entries as $k => $v) {
                $log_id = is_numeric($log_id = $v) ? (int) $log_id : 0;
                if ($log_id !== 0) {
                    $list[$k] = $log_id;
                }
            }

            $params['sql'] = 'AND L.log_id IN(' . implode(',', $list) . ') ';
        }

        $this->rs = App::log()->getLogs($params);
    }
}
