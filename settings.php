<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Global settings.
 *
 * @package    local_autolinker
 * @copyright  2021 Pressbooks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if (!isset($hassiteconfig)) {
    $hassiteconfig = has_capability('moodle/site:config', context_system::instance());
}

if ($hassiteconfig) {

    $settings = new admin_settingpage(
        'localautolinker',
        new lang_string('settingstitle', 'local_autolinker')
    );

    $settings->add(
        new admin_setting_configcheckbox(
            'local_autolinker/enable',
            new lang_string('enable', 'local_autolinker'),
            new lang_string('enable_desc', 'local_autolinker'),
            '0'
        )
    );

    /** @var admin_root $ADMIN */
    $ADMIN->add('localplugins', $settings);
}
