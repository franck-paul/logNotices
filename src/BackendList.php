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

use Dotclear\App;
use Dotclear\Core\Backend\Listing\Listing;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Strong;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Th;
use Dotclear\Helper\Html\Form\Thead;
use Dotclear\Helper\Html\Form\Tr;
use Dotclear\Helper\Html\Html;

class BackendList extends Listing
{
    public function display(int $page, int $nb_per_page, string $enclose_block = '', bool $filter = false): void
    {
        if ($this->rs->isEmpty()) {
            echo (new Para())
                ->items([
                    (new Strong($filter ? __('No notice matches the filter') : __('No notice'))),
                ])
            ->render();
        } else {
            $pager = App::backend()->listing()->pager($page, (int) $this->rs_count, $nb_per_page, 10)->getLinks();

            /**
             * @var array<string>
             */
            $entries = isset($_REQUEST['entries']) && is_array($entries = $_REQUEST['entries']) ? $entries : [];

            $selected = [];
            if ($entries !== []) {
                foreach ($entries as $value) {
                    $index = is_numeric($index = $value) ? (int) $value : 0;
                    if ($index !== 0) {
                        $selected[$index] = true;
                    }
                }
            }

            if ($filter) {
                $caption = sprintf(__('List of %s notices matching the filter.'), $this->rs_count);
            } else {
                $caption = sprintf(__('List of notices (%s)'), $this->rs_count);
            }

            $lines = function () use ($selected) {
                while ($this->rs->fetch()) {
                    $log_id = is_numeric($log_id = $this->rs->log_id) ? (int) $log_id : 0;
                    if ($log_id !== 0) {
                        $user_id   = is_string($user_id = $this->rs->user_id) ? $user_id : '';
                        $blog_id   = is_string($blog_id = $this->rs->blog_id) ? $blog_id : '';
                        $log_table = is_string($log_table = $this->rs->log_table) ? $log_table : '';
                        $log_dt    = is_string($log_dt = $this->rs->log_dt) ? $log_dt : '';
                        $log_ip    = is_string($log_ip = $this->rs->log_ip) ? $log_ip : '';
                        $log_msg   = is_string($log_msg = $this->rs->log_msg) ? $log_msg : '';
                        $user_tz   = is_string($user_tz = App::auth()->getInfo('user_tz')) ? $user_tz : null;

                        yield (new Tr('p' . $log_id))
                            ->class('line')
                            ->cols([
                                (new Td())
                                    ->class('nowrap')
                                    ->items([
                                        (new Checkbox(['entries[]'], isset($selected[$log_id])))
                                            ->value($log_id),
                                    ]),
                                (new Td())
                                    ->class('nowrap')
                                    ->text(Html::escapeHTML($user_id)),
                                (new Td())
                                    ->class('nowrap')
                                    ->text(Html::escapeHTML($blog_id)),
                                (new Td())
                                    ->class('nowrap')
                                    ->text(Html::escapeHTML($log_table)),
                                (new Td())
                                    ->class(['nowrap', 'count'])
                                    ->text(Date::str(__('%Y/%m/%d %H:%M:%S'), strtotime($log_dt), $user_tz)),
                                (new Td())
                                    ->class('nowrap')
                                    ->text(Html::escapeHTML($log_ip)),
                                (new Td())
                                    ->class('maximal')
                                    ->text(Html::escapeHTML($log_msg)),
                            ]);
                    }
                }
            };

            $buffer = (new Div())
                ->class('table-outer')
                ->items([
                    (new Table())
                        ->caption((new Caption($caption)))
                        ->thead((new Thead())
                            ->rows([
                                (new Tr())
                                    ->cols([
                                        (new Th())
                                            ->class('first')
                                            ->colspan(2)
                                            ->text(__('User')),
                                        (new Th())
                                            ->scope('col')
                                            ->text(__('Blog')),
                                        (new Th())
                                            ->scope('col')
                                            ->text(__('Log type')),
                                        (new Th())
                                            ->scope('col')
                                            ->text(__('Date')),
                                        (new Th())
                                            ->scope('col')
                                            ->text(__('IP')),
                                        (new Th())
                                            ->scope('col')
                                            ->text(__('Message')),
                                    ]),
                            ]))
                        ->tbody((new Tbody())
                            ->rows([
                                ... $lines(),
                            ])),
                ])
            ->render();

            if ($enclose_block !== '') {
                $buffer = sprintf($enclose_block, $buffer);
            }

            echo $pager . $buffer . $pager;
        }
    }
}
