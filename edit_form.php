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
 * minimalistic edit form
 *
 * @package   block_course_descendants
 * @category  blocks
 * @copyright 2013 Valery Fremaux / valery.fremaux@gmail.com
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class block_course_descendants_edit_form extends block_edit_form {

    public function specific_definition($mform) {

        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_blocktitle', get_string('blocktitle', 'block_course_descendants'));
		$mform->setType('config_blocktitle', PARAM_TEXT);
        $mform->setDefault('config_blocktitle', 'Class pages');

        $mform->addElement('selectyesno', 'config_showdescription', get_string('showdescription', 'block_course_descendants'));
		$mform->setDefault('config_showdescription', '0');
        //$mform->setType('config_showdescription', PARAM_INTEGER);
		
		$mform->addElement('selectyesno', 'config_showcourseimage', get_string('showcourseimage', 'block_course_descendants'));
		$mform->setDefault('config_showcourseimage', '1');
        //$mform->setType('config_showcourseimage', PARAM_INTEGER);
		
		$mform->addElement('selectyesno', 'config_showcoursecontact', get_string('showcoursecontact', 'block_course_descendants'));
		$mform->setDefault('config_showcoursecontact', '1');
        //$mform->setType('config_showcoursecontact', PARAM_INTEGER);

        $mform->addElement('selectyesno', 'config_checkenrollment', get_string('checkenrollment', 'block_course_descendants'));
		$mform->setDefault('config_checkenrollment', '1');
        //$mform->setType('config_checkenrollment', PARAM_INTEGER);

        $label = get_string('stringlimit', 'block_course_descendants');
        $mform->addElement('text', 'config_stringlimit', $label, array('size' => 4, 'maxlength' => 3));
		$mform->setType('config_stringlimit', PARAM_INTEGER);
		
		/*$label = get_string('heightlimit', 'block_course_descendants');
        $mform->addElement('text', 'config_heightlimit', $label, array('size' => 4, 'maxlength' => 3));
        $mform->setType('config_heightlimit', PARAM_INTEGER);*/

    }
}
