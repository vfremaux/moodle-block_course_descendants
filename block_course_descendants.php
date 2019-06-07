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
        global $CFG, $COURSE, $OUTPUT, $USER, $DB; //Included CFG to be able to get contacts
		require_once($CFG->dirroot.'/user/lib.php');

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
		
		/*Not sure how to do this, I want to be able to change the max height of the content-div to the height limit. Then set overflow-y to scroll.
		$heightlimit = $this->config->heightlimit;
		*/

        // Fetch direct ascendants that are metas who point the current course as descendant.
        // Changed so that anyone who can configure the block can see all the classes (e.g. teachers, useful for teachers to be able to see each others classes)
		// Changed the query so the only enabled enrolment methods are used
        if (!empty($this->config->checkenrollment) && !has_capability('block/course_descendants:configure', $blockcontext)) { 
            $sql = "
                 SELECT DISTINCT
                    c.id,
                    c.shortname,
                    c.fullname,
                    c.sortorder,
                    c.visible,
                    c.summary,
                    c.summaryformat,
                    cc.name as catname,
                    cc.id as catid,
                    cc.visible as catvisible,
					cc.sortorder
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
					e.status = 0 AND 
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
                    c.summaryformat,
                    cc.id as catid,
                    cc.name as catname,
                    cc.visible as catvisible,
					cc.sortorder
                 FROM
                     {course} c,
                     {course_categories} cc,
                     {enrol} e
                 WHERE
                    cc.id = c.category AND
                    e.courseid = ? AND
                    e.enrol = 'meta' AND
					e.status = 0 AND
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
				
				/* Edited so that categories are no longer shown - maybe this should be in config
                if ($categorymem != $descendant->catname) {
                    $categorymem = $descendant->catname;
                    $this->content->items[] = '<b>'.format_string($descendant->catname).'</b>';
                } 
				*/

                $context = context_course::instance($descendant->id);
                if ($descendant->visible || has_capability('moodle/course:viewhiddencourses', $context)) {
					
                    $icon  = ''; //Can have an Icon
                    $this->content->icons[] = $icon;

                    if (!empty($this->config->stringlimit)) {
                        $fullname = shorten_text(format_string($descendant->fullname), 0 + @$this->config->stringlimit);
                    } else {
                        $fullname = format_string($descendant->fullname);
                    }

                    $coursename = format_string($descendant->fullname);
                    $courseurl = new moodle_url('/course/view.php', array('id' => $descendant->id));
					
					/*Start of large div block for each descendant*/
                    $item = '<div class="descdescendant">';
					
					/* Need to get proper course object to get course image */
					if ($descendant instanceof stdClass) {
            			require_once($CFG->libdir. '/coursecatlib.php');
            			$descendant = new course_in_list($descendant);
       				}
					
					/* Show Course Image */
					if ($this->config->showcourseimage == 1) {
                        $description = format_text($descendant->summary);
						$courseimage = '';
						foreach ($descendant->get_course_overviewfiles() as $file) {
							$isimage = $file->is_valid_image();
							$url = file_encode_url("$CFG->wwwroot/pluginfile.php",
								'/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
							$file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
							if ($isimage) {
								$courseimage = '<a title="' .$coursename.'" href="'.$courseurl.'"><div class="descimagesmall" style="background-image: url('.$url.');"></div></a>';
							} 
						}
						$item .= '<div class="desccourseimage">'.$courseimage.'</div>';
					}
					
					$item .= '<div class="descdetails">';
					
					/* show course name */
					$item .= '<div class="desctitle"><a title="'.$coursename.'"href="'.$courseurl.'">'.$coursename.'</a></div>';
				
					/* show course contacts */
					if ($this->config->showcoursecontact == 1) {
					
						$item .= '<div class="desccontacts">';
						$current_role = '';
						$i = 0;
						$list_course_contacts = $descendant->get_course_contacts();

						foreach ($list_course_contacts as $userid => $coursecontact) {
							if ($i == 0) {
								$current_role = $coursecontact['rolename']; /*sets to teacher */
								$item .= '<span class="desccurrentrole">'.$current_role.'</span>: ';
								$name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
								$item .= '<span class="desccontact">';
								/*TODO INSERT USERPICTURE IF $this->config->showcontact*/
								/*
								$user = core_user::get_user($userid, '*', MUST_EXIST);
								$item .= $OUTPUT->user_picture($user, array('size' => 80));
								*/
								$item .= $name;

							}
							if (($i > 0) AND ($coursecontact['rolename'] == $current_role)) {
								$item .= '</span>';
								$item .= ', ';
								$item .= '<span class="desccontact">';
								$name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
								/*TODO INSERT USERPICTURE IF $this->config->showcontact*/
								/*
								$user = core_user::get_user($userid, '*', MUST_EXIST);
								$item .= $OUTPUT->user_picture($user, array('size' => 80)); 
								*/
								$item .= $name;

							}
							else if ($i > 0) { //no longer the same role, get new role
								$item .= '</span>';
								$current_role = $coursecontact['rolename']; /*sets to next role */
								$item .= '<span class="desccurrentrole">'.$current_role.'</span>: ';

								$item .= '<span class="desccontact">';
								$current_role = $coursecontact['rolename'];
								$item .= $current_role.': ';
								$name = html_writer::link(new moodle_url('/user/view.php', array('id' => $userid, 'course' => SITEID)), $coursecontact['username']);
								/*TODO INSERT USERPICTURE IF $this->config->showcontact*/
								/*
								$user = core_user::get_user($userid, '*', MUST_EXIST);
								$item .= $OUTPUT->user_picture($user, array('size' => 80));
								*/
								$item.= $name; 
							}
							$i++;
						}
						$item .= '</span>';
						$item .= '</div>';
					}
					
					/* show description */
					if ($this->config->showdescription == 1) {
                        $description = format_text($descendant->summary);
                        $item .= '<div class="descdescription">'.$description.'</div>';
                    }
					
					$item .= '</div>';
					
					//Close Descendant Div block
					$item .= '</div>'; 
                    $this->content->items[] = $item;
					
                }
				
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

		//In my application I have given this permission to teachers
        if (has_capability('block/course_descendants:configure', $context)) {
            return true;
        }

        return false;
    }
}
