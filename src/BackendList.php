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
use dcCore;
use Dotclear\Core\Backend\Listing\Listing;
use Dotclear\Core\Backend\Listing\Pager;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Html;
use form;

class BackendList extends Listing
{
    public function display(int $page, int $nb_per_page, string $enclose_block = '', bool $filter = false): void
    {
        if ($this->rs->isEmpty()) {
            if ($filter) {
                echo '<p><strong>' . __('No notice matches the filter') . '</strong></p>';
            } else {
                echo '<p><strong>' . __('No notice') . '</strong></p>';
            }
        } else {
            $pager   = new Pager($page, $this->rs_count, $nb_per_page, 10);
            $entries = [];
            if (isset($_REQUEST['entries'])) {
                foreach ($_REQUEST['entries'] as $v) {
                    $entries[(int) $v] = true;
                }
            }
            $html_block = '<div class="table-outer">' .
                '<table>';

            if ($filter) {
                $html_block .= '<caption>' . sprintf(__('List of %s notices matching the filter.'), $this->rs_count) . '</caption>';
            } else {
                $html_block .= '<caption>' . sprintf(__('List of notices (%s)'), $this->rs_count) . '</caption>';
            }

            $cols = [
                'user'    => '<th colspan="2" class="first">' . __('User') . '</th>',
                'blog'    => '<th scope="col">' . __('Blog') . '</th>',
                'type'    => '<th scope="col">' . __('Log type') . '</th>',
                'date'    => '<th scope="col">' . __('Date') . '</th>',
                'ip'      => '<th scope="col">' . __('IP') . '</th>',
                'message' => '<th scope="col">' . __('Message') . '</th>',
            ];
            $cols = new ArrayObject($cols);
            $html_block .= '<tr>' . implode(iterator_to_array($cols)) . '</tr>%s</table></div>';
            if ($enclose_block) {
                $html_block = sprintf($enclose_block, $html_block);
            }
            $blocks = explode('%s', $html_block);

            echo $pager->getLinks();
            echo $blocks[0];
            while ($this->rs->fetch()) {
                echo $this->postLine(isset($entries[$this->rs->log_id]));
            }
            echo $blocks[1];
            echo $pager->getLinks();
        }
    }

    private function postLine(bool $checked): string
    {
        $res = '<tr class="line"' . ' id="p' . $this->rs->log_id . '">';

        $cols = [
            'check' => '<td class="nowrap">' .
            form::checkbox(['entries[]'], $this->rs->log_id, $checked, '', '') .
            '</td>',
            'user' => '<td class="nowrap">' . Html::escapeHTML($this->rs->user_id) . '</td>',
            'blog' => '<td class="nowrap">' . Html::escapeHTML($this->rs->blog_id) . '</td>',
            'type' => '<td class="nowrap">' . Html::escapeHTML($this->rs->log_table) . '</td>',
            'date' => '<td class="nowrap count">' .
            Date::str(
                __('%Y/%m/%d %H:%M:%S'),
                strtotime($this->rs->log_dt),
                dcCore::app()->auth->getInfo('user_tz')
            ) .
            '</td>',
            'ip'      => '<td class="nowrap">' . $this->rs->log_ip . '</td>',
            'message' => '<td class="maximal">' . Html::escapeHTML($this->rs->log_msg) . '</td>',
        ];
        $cols = new ArrayObject($cols);

        $res .= implode(iterator_to_array($cols));
        $res .= '</tr>';

        return $res;
    }
}
