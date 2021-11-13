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
 * Add event handlers for the assign
 *
 * @package    local_autolinker
 * @category   event
 * @copyright  2021 Pressbooks
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_autolinker;

use core\event\course_restored;
use dml_exception;
use Exception;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/mod/lti/lib.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');

/**
 * class observer
 */
abstract class observer {

    /**
     * React when course is restored.
     *
     * @param course_restored $event
     * @return void
     * @throws dml_exception
     */
    public static function course_restored(course_restored $event): void {
        global $DB;
        $enabled = get_config('local_autolinker', 'enable');
        if ($enabled === '1') {
            try {
                $mods = get_coursemodules_in_course('lti', $event->objectid, 'm.id AS ltiid, m.typeid, m.toolurl');
                foreach ($mods as $mod) {
                    $currenttypeid = intval($mod->typeid);
                    $newtypeid = self::lti_tool($currenttypeid, $mod->toolurl);
                    if ($newtypeid != $currenttypeid) {
                        $DB->update_record('lti', (object)['id' => $mod->ltiid, 'typeid' => $newtypeid]);
                    }
                    // Make sure activity grade is initialized.
                    $lti = $DB->get_record('lti', ['id' => $mod->ltiid]);
                    if ($lti !== false) {
                        $lti->cmidnumber = $mod->idnumber;
                        lti_grade_item_update($lti);
                    }
                }
                // @codingStandardsIgnoreLine
            } catch (Exception $exception) {
                // We are just silencing any potential exception.
            }
        }
    }

    /**
     * Get the correct tool.
     *
     * @param int $typeid
     * @param string $toolurl
     * @return int
     */
    public static function lti_tool(int $typeid, string $toolurl): int {
        if (($typeid == 0) && ($tool = lti_get_tool_by_url_match($toolurl))) {
            $typeid = intval($tool->id);
        }
        return $typeid;
    }
}
