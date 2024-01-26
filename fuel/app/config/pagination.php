<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.5
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2013 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */


return array(

	// the active pagination template
	'active'                      => 'default',
	//'active'                      => 'bootstrap',
	// 'active'                      => 'paginglist',

	// default FuelPHP pagination template, compatible with pre-1.4 applications
	'default'                     => array(
		'wrapper'                 => "<div class=\"pagination\">\n\t{pagination}\n\t<p style=\"margin-left:20px;\">{pagenumber}&nbsp;ページ</p></div>\n",

		'first'                   => "<span class=\"first\">\n\t{link}\n</span>\n",
        'first-marker'            => "&lt;&lt;",
		'first-link'              => "\t\t<a href=\"{uri}\">{page}</a>\n",

		'previous'                => "<span class=\"previous\">\n\t{link}\n</span>\n",
        'previous-marker'         => "&lt;",
		'previous-link'           => "\t\t<a href=\"{uri}\">{page}</a>\n",

		'previous-inactive'       => "<span class=\"previous-inactive\">\n\t{link}\n</span>\n",
		'previous-inactive-link'  => "\t\t<a href=\"{uri}\">{page}</a>\n",

		'regular'                 => "<span>\n\t{link}\n</span>\n",
		'regular-link'            => "\t\t<a href=\"{uri}\">{page}</a>\n",

		'active'                  => "<span class=\"active\">\n\t{link}\n</span>\n",
		'active-link'             => "\t\t<a href=\"{uri}\">{page}</a>\n",

		'next'                    => "<span class=\"next\">\n\t{link}\n</span>\n",
        'next-marker'             => "&gt;",
		'next-link'               => "\t\t<a href=\"{uri}\">{page}</a>\n",

		'next-inactive'           => "<span class=\"next-inactive\">\n\t{link}\n</span>\n",
		'next-inactive-link'      => "\t\t<a href=\"{uri}\">{page}</a>\n",

		'last'                    => "<span class=\"last\">\n\t{link}\n</span>\n",
        'last-marker'             => "&gt;&gt;",
		'last-link'               => "\t\t<a href=\"{uri}\">{page}</a>\n",
	),

	// Twitter bootstrap 2.x template
	'bootstrap'                   => array(
		'wrapper'                 => "<div class=\"pagination\">\n\t<ul>{pagination}\n\t</ul>\n</div>\n",

		'first'                   => "\n\t\t<li>{link}</li>",
		'first-link'              => "<a href=\"{uri}\">{page}</a>",

		'previous'                => "\n\t\t<li>{link}</li>",
		'previous-link'           => "<a href=\"{uri}\">{page}</a>",

		'previous-inactive'       => "\n\t\t<li class=\"disabled\">{link}</li>",
		'previous-inactive-link'  => "<a href=\"{uri}\">{page}</a>",

		'regular'                 => "\n\t\t<li>{link}</li>",
		'regular-link'            => "<a href=\"{uri}\">{page}</a>",

		'active'                  => "\n\t\t<li class=\"active\">{link}</li>",
		'active-link'             => "<a href=\"{uri}\">{page}</a>",

		'next'                    => "\n\t\t<li>{link}</li>",
		'next-link'               => "<a href=\"{uri}\">{page}</a>",

		'next-inactive'           => "\n\t\t<li class=\"disabled\">{link}</li>",
		'next-inactive-link'      => "<a href=\"{uri}\">{page}</a>",

		'last'                    => "\n\t\t<li>{link}</li>",
		'last-link'               => "<a href=\"{uri}\">{page}</a>",
	),

	// Master Member
	'paginglist'                  => array(
		'wrapper'                 => "<ul class=\"pager-ul floatright\">{pagination}</ul>",
		'first'                   => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-l2\">{link}</a></li>",
		'first-marker'            => "&nbsp;",
		'first-link'              => "{page}",
		'first-inactive'          => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-l2\">{link}</a></li>",
		'first-inactive-link'     => "{page}",
		'previous'                => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-l\">{link}</a></li>",
		'previous-marker'         => "&nbsp;",
		'previous-link'           => "{page}",
		'previous-inactive'       => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-l\">{link}</a></li>",
		'previous-inactive-link'  => "{page}",
		'regular'                 => "<li class=\"pager-li\"><a href=\"{uri}\">{link}</a></li>",
		'regular-link'            => "{page}",
		'active'                  => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-active\">{link}</a></li>",
		'active-link'             => "{page}",
		'next'                    => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-r\">{link}</a></li>",
		'next-marker'             => "&nbsp;",
		'next-link'               => "{page}",
		'next-inactive'           => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-r\">{link}</a></li>",
		'next-inactive-link'      => "{page}",
		'last'                    => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-r2\">{link}</a></li>",
		'last-marker'             => "&nbsp;",
		'last-link'               => "{page}",
		'last-inactive'           => "<li class=\"pager-li\"><a href=\"{uri}\" class=\"pager-arrow-r2\">{link}</a></li>",
		'last-inactive-link'      => "{page}",
	),

	// Master Member
	'paginglist2'                  => array(
		'wrapper'                 => "<div><ul class=\"pager-ul\"><li class=\"pager-li\">\n\t{pagination}\n</li></ul></div>\n",
		'first'                   => "<a href=\"{uri}\" class=\"pager-arrow-l2\">\n\t{link}\n</a>\n",
		'first-marker'            => "&nbsp;",
		'first-link'              => "{page}",
		'first-inactive'          => "<a href=\"{uri}\" class=\"pager-active\">\n\t{link}\n</a>\n",
		'first-inactive-link'     => "{page}",
		'previous'                => "<a href=\"{uri}\" class=\"pager-arrow-l\">\n\t{link}\n</a>\n",
		'previous-marker'         => "&nbsp;",
		'previous-link'           => "{page}",
		'previous-inactive'       => "<a href=\"{uri}\" class=\"pager-active\">\n\t{link}\n</a>\n",
		'previous-inactive-link'  => "{page}",
		'regular'                 => "<a href=\"{uri}\">\n\t{link}\n</a>\n",
		'regular-link'            => "{page}",
		'active'                  => "<a href=\"{uri}\" class=\"pager-active\">\n\t{link}\n</a>\n",
		'active-link'             => "{page}",
		'next'                    => "<a href=\"{uri}\" class=\"pager-arrow-r\">\n\t{link}\n</a>\n",
		'next-marker'             => "&nbsp;",
		'next-link'               => "{page}",
		'next-inactive'           => "<a href=\"{uri}\" class=\"pager-active\">\n\t{link}\n</a>\n",
		'next-inactive-link'      => "{page}",
		'last'                    => "<a href=\"{uri}\" class=\"pager-arrow-r2\">\n\t{link}\n</a>\n",
		'last-marker'             => "&nbsp;",
		'last-link'               => "{page}",
		'last-inactive'           => "<a href=\"{uri}\" class=\"pager-active\">\n\t{link}\n</a>\n",
		'last-inactive-link'      => "{page}",
	),

);
