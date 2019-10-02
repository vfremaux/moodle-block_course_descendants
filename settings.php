<?php
	$settings->add(new admin_setting_title(
            'block_course_descendants/blocktitle',
            get_string('blocktitle', 'block_course_descendants'),
            'description','Class pages'
        ));
 

	$settings->add(new admin_setting_configtext(
            'block_course_descendants/showcourseimage',
            get_string('showcourseimage', 'block_course_descendants'),
            'description',
            '1',
            PARAM_INT
        ));

	$settings->add(new admin_setting_configtext(
            'block_course_descendants/showcoursecontact',
            get_string('showcoursecontact', 'block_course_descendants'),
            'description',
            '1',
            PARAM_INT
        ));

	$settings->add(new admin_setting_configtext(
            'block_course_descendants/checkenrollment',
            get_string('checkenrollment', 'block_course_descendants'),
            'description',
            '1',
            PARAM_INT
        ));

