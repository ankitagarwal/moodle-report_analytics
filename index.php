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
 * Displays live view of recent logs
 *
 * This file generates live view of recent logs.
 *
 * @package    report_analytics
 * @copyright  2015 onwards Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/course/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);

if (empty($id)) {
    require_login();
    $context = context_system::instance();
    $coursename = format_string($SITE->fullname, true, array('context' => $context));
} else {
    $course = get_course($id);
    require_login($course);
    $context = context_course::instance($course->id);
    $coursename = format_string($course->fullname, true, array('context' => $context));
}
require_capability('report/analytics:view', $context);

$params = array();
if ($courseid != 0) {
    $params['id'] = $courseid;
}
$url = new moodle_url("/report/analytics/index.php", $params);

if (empty($id)) {
    admin_externalpage_setup('reportanalytics', '', null, '', array('pagelayout' => 'report'));
}

$PAGE->set_url($url);
$PAGE->set_pagelayout('report');
$PAGE->set_url($url);
$PAGE->set_context($context);
$PAGE->set_title($coursename);
$PAGE->set_heading($coursename);

$logreader = get_log_manager()->get_readers('\core\log\sql_select_reader');
$logreader = reset($logreader);

$renderable = new \report_analytics\output\renderable($logreader, $courseid);
$output = $PAGE->get_renderer('report_analytics');
echo $output->header();
echo $output->render($renderable);

// Trigger a logs viewed event.
$event = \report_analytics\event\report_viewed::create(array('context' => $context));
$event->trigger();

echo $output->footer();