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
 * @package     block_course_descendants
 * @author      Valery Fremaux <valery.fremaux@gmail.com>
 * @copyright   2013 Valery Fremaux (https://www.activeprolearn.com)
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for block settings edition.
 */
class block_course_descendants_edit_form extends block_edit_form {
    /**
     * Block's specific définition.
     */
    public function specific_definition($mform) {
        $mform->addElement('header', 'configheader', get_string('blocksettings', 'block'));

        $mform->addElement('text', 'config_blocktitle', get_string('configblocktitle', 'block_course_descendants'));
        $mform->setType('config_blocktitle', PARAM_CLEANHTML);

        $lbl = get_string('configshowdescription', 'block_course_descendants');
        $mform->addElement('advcheckbox', 'config_showdescription', $lbl);

        $lbl = get_string('configcheckenrollment', 'block_course_descendants');
        $mform->addElement('advcheckbox', 'config_checkenrollment', $lbl);
        $mform->setDefault('config_checkenrollment', 1);

        $label = get_string('configstringlimit', 'block_course_descendants');
        $mform->addElement('text', 'config_stringlimit', $label, ['size' => 4, 'maxlength' => 3]);
        $mform->setType('config_stringlimit', PARAM_INT);
    }
}
