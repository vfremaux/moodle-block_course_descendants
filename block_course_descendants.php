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
 * Main class
 *
 * @package    block_course_descendants
 * @category   blocks
 * @copyright  2O13 Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

class block_course_descendants extends block_list {

    public function init() {
        $this->title = get_string('title', 'block_course_descendants');
    }

    public function has_config() {
        return false;
    }

    public function instance_allow_config() {
        return true;
    }

    public function applicable_formats() {
        return array('all' => false, 'course' => true, 'site' => false);
    }

    public function specialization() {
        if (!empty($this->config->blocktitle)) {
            $this->title = format_string($this->config->blocktitle);
        } else {
            $this->title = '';
        }
    }

    public function get_content() {
        global $COURSE, $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        $blockcontext = context_block::instance($this->instance->id);

        if (!enrol_is_enabled('meta')) {
            if (has_capability('block/course_descendants:configure', $blockcontext)) {
                $this->content = new stdClass;
                $this->content->items = array();
                $this->content->icons = array();
                $this->content->footer = '<div class="error">'.get_string('metasnotenabled', 'block_course_descendants').'</div>';
            } else {
                $this->content = new stdClass;
                $this->content->items = array();
                $this->content->icons = array();
                $this->content->footer = '';
                $this->title = '';
            }
        }

        // Fetch direct ascendants that are metas who point the current course as descendant.
        // Admin sees all descendants.
        $isadmin = has_capability('moodle/site:config', context_system::instance());
        $needcheckenrol = !empty($this->config->checkenrollment);

        if ($needcheckenrol && !$isadmin) {
            $sql = "
                 SELECT DISTINCT
                    c.id,
                    c.shortname,
                    c.fullname,
                    c.sortorder,
                    c.visible,
                    c.enddate,
                    c.summary,
                    c.summaryformat,
                    cc.name as catname,
                    cc.id as catid,
                    cc.visible as catvisible
                 FROM
                     {course} c,
                     {course_categories} cc,
                     {enrol} e,
                     {context} co,
                     {role_assignments} ra
                 WHERE
                    cc.id = c.category AND
                    e.customint1 = c.id AND
                    e.courseid = ? AND
                    e.enrol = 'meta' AND
                    co.instanceid = c.id AND
                    co.contextlevel = ".CONTEXT_COURSE." AND
                    ra.contextid = co.id AND
                    ra.userid = {$USER->id}
                 ORDER BY
                     cc.sortorder,
                     c.sortorder
            ";
        } else {
            $sql = "
                 SELECT DISTINCT
                    c.id,
                    c.shortname,
                    c.fullname,
                    c.sortorder,
                    c.visible,
                    c.summary,
                    c.enddate,
                    c.summaryformat,
                    cc.id as catid,
                    cc.name as catname,
                    cc.visible as catvisible
                 FROM
                     {course} c,
                     {course_categories} cc,
                     {enrol} e
                 WHERE
                    cc.id = c.category AND
                    e.courseid = ? AND
                    e.enrol = 'meta' AND
                    e.customint1 = c.id
                 ORDER BY
                     cc.sortorder,
                     c.sortorder
            ";
        }

        $descendants = $DB->get_records_sql($sql, array($COURSE->id));

        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        if ($descendants) {
            $categorymem = '';
            foreach ($descendants as $descendant) {

                $catcontext = context_coursecat::instance($descendant->catid);
                if (!$descendant->catvisible && !has_capability('moodle/category:viewhiddencategories', $catcontext)) {
                    continue;
                }

                if ($categorymem != $descendant->catname) {
                    $categorymem = $descendant->catname;
                    $this->content->items[] = '<b>'.format_string($descendant->catname).'</b>';
                }

                // TODO : check visibility on course.
                $context = context_course::instance($descendant->id);
                $canseehidden = has_capability('moodle/course:viewhiddencourses', $context);
                $canedit = has_capability('moodle/course:manageactivities', $context);
                if (!$descendant->visible && !($canseehidden || $canedit)) {
                    continue;
                }

                // Check to see if past class, if so hide.
                if (!empty($descendant->enddate) && ($descendant->enddate < time()) && !($canseehidden || $canedit)) {
                    continue;
                }

                $icon  = '';
                $this->content->icons[] = $icon;

                if (!empty($this->config->stringlimit)) {
                    $fullname = shorten_text(format_string($descendant->fullname), 0 + @$this->config->stringlimit);
                } else {
                    $fullname = format_string($descendant->fullname);
                }

                $coursename = format_string($descendant->fullname);
                $courseurl = new moodle_url('/course/view.php', array('id' => $descendant->id));
                $item = '<a title="' .$coursename.'" href="'.$courseurl.'">'.$coursename.'</a>';
                if (!empty($this->config->showdescription)) {
                    $description = format_text($descendant->summary);
                    $item .= '<div class="block-descendants course-description">'.$description.'</div>';
                }
                $this->content->items[] = $item;
            }
        } else {
            // If no descendants, make block invisible for everyone except when editing.
            $this->title = '';
        }

        return $this->content;
    }

    /**
     * Serialize and store config data
     */
    public function instance_config_save($data, $nolongerused = false) {

        if (!isset($data->showdescription)) {
            $data->showdescription = 0;
        }
        if (!isset($data->checkenrollment)) {
            $data->checkenrollment = 0;
        }

        parent::instance_config_save($data, false);
    }

    /**
     *
     */
    public function user_can_edit() {
        global $COURSE;

        $context = context_course::instance($COURSE->id);

        if (has_capability('block/course_descendants:configure', $context)) {
            return true;
        }

        return false;
    }
}
