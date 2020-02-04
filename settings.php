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
 * @package    block_course_ascendants
 * @category   blocks
 * @copyright  2012 onwards Valery Fremaux (valery.fremaux@gmail.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$yesnooptions[0] = get_string('no');
$yesnooptions[1] = get_string('yes');

$key = 'block_course_descendant/showcoursecontact';
$label = get_string('showcoursecontact', 'block_course_descendant');
$desc = '';
$settings->add(new admin_setting_configselect($key, $label, $desc, 0, $yesnooptions));

$key = 'block_course_descendant/showcourseimage';
$label = get_string('showcourseimage', 'block_course_descendant');
$desc = '';
$settings->add(new admin_setting_configselect($key, $label, $desc, 1, $yesnooptions));

$key = 'block_course_descendant/showdescription';
$label = get_string('configshowdescription', 'block_course_descendant');
$desc = '';
$settings->add(new admin_setting_configselect($key, $label, $desc, 0, $yesnooptions));

$settings->add(new admin_setting_title('block_course_descendants/blocktitle',
            get_string('blocktitle', 'block_course_descendants'),
            'description','Class pages'));

$key = 'block_course_descendant/checkenrollment';
$label = get_string('checkenrollment', 'block_course_descendant');
$desc = '';
$settings->add(new admin_setting_configselect($key, $label, $desc, 0, $yesnooptions));

