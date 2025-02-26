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
use Dotclear\Core\Backend\Listing\Pager;
use Dotclear\Helper\Date;
use Dotclear\Helper\Html\Form\Caption;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Div;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Table;
use Dotclear\Helper\Html\Form\Tbody;
use Dotclear\Helper\Html\Form\Td;
use Dotclear\Helper\Html\Form\Text;
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
                    (new Text('strong', $filter ? __('No notice matches the filter') : __('No notice'))),
                ])
            ->render();
        } else {
            $pager = (new Pager($page, (int) $this->rs_count, $nb_per_page, 10))->getLinks();

            $entries = [];
            if (isset($_REQUEST['entries'])) {
                foreach ($_REQUEST['entries'] as $v) {
                    $entries[(int) $v] = true;
                }
            }

            if ($filter) {
                $caption = sprintf(__('List of %s notices matching the filter.'), $this->rs_count);
            } else {
                $caption = sprintf(__('List of notices (%s)'), $this->rs_count);
            }

            $lines = function ($rs, $entries) {
                while ($rs->fetch()) {
                    $checked = isset($entries[$this->rs->log_id]);
                    yield (new Tr('p' . $rs->log_id))
                        ->class('line')
                        ->cols([
                            (new Td())
                                ->class('nowrap')
                                ->items([
                                    (new Checkbox(['entries[]'], $checked))
                                        ->value($this->rs->log_id),
                                ]),
                            (new Td())
                                ->class('nowrap')
                                ->text(Html::escapeHTML($this->rs->user_id)),
                            (new Td())
                                ->class('nowrap')
                                ->text(Html::escapeHTML($this->rs->blog_id)),
                            (new Td())
                                ->class('nowrap')
                                ->text(Html::escapeHTML($this->rs->log_table)),
                            (new Td())
                                ->class(['nowrap', 'count'])
                                ->text(Date::str(__('%Y/%m/%d %H:%M:%S'), strtotime((string) $rs->log_dt), App::auth()->getInfo('user_tz'))),
                            (new Td())
                                ->class('nowrap')
                                ->text(Html::escapeHTML($this->rs->log_ip)),
                            (new Td())
                                ->class('maximal')
                                ->text(Html::escapeHTML($this->rs->log_msg)),
                        ]);
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
                                ... $lines($this->rs, $entries),
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
