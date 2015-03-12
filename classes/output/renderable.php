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
 * Analytics report renderable class.
 *
 * @package    report_analytics
 * @copyright  2015 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace report_analytics\output;
defined('MOODLE_INTERNAL') || die;

/**
 * Report analytics renderable class.
 *
 * @since      Moodle 2.7
 * @package    report_analytics
 * @copyright  2015 onwards Ankit Agarwal <ankit.agrr@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class renderable implements \renderable {

    const LOG_LOOK_BEHIND_DAYS = 90;

    /**
     * @var \core\log\sql_select_reader
     */
    private $logreader;

    private $courseid;

    public function __construct (\core\log\sql_select_reader $logreader, $courseid) {

        $this->logreader = $logreader;
        $this->courseid = $courseid;
    }

    public function get_log_data() {
        $return = array();
        $now = time();
        // Moodle Y U NO SUPPORT THIS ? Assume they are using Gregorian calendar.
        $beginofday = strtotime("midnight", $now);
        $datetime = $beginofday;
        $readsqlwhere = "Courseid = ? AND CRUD = 'r' AND (timecreated < ? AND timecreated > ?)";
        $writesqlwhere = "Courseid = ? AND CRUD <> 'r' AND (timecreated < ? AND timecreated > ?)";
        $params = array('courseid' => $this->courseid);
        for ($i = self::LOG_LOOK_BEHIND_DAYS; $i > 0; $i--) {
            $params[] = $datetime;
            $params[] = $datetime = strtotime("-1 day", $datetime);
            $readcount = $this->logreader->get_events_select_count($readsqlwhere, $params);
            $writecount = $this->logreader->get_events_select_count($writesqlwhere, $params);
            $month = date('m', $datetime); // 1-12
            $day = date('d', $datetime); // 1-31
            $return[$month][$day] = array('read' => $readcount, 'write' => $writecount);
        }
        return $return;
    }
}