<?php //$Id: block_course_descendants.php,v 1.4 2012-07-18 16:10:13 vf Exp $

class block_course_descendants extends block_list {
    function init() {
        $this->title = get_string('title', 'block_course_descendants');
    }

    function has_config() {
        return false;
    }
    
    function instance_allow_config() {
        return true;
    }

    function applicable_formats() {
        return array('all' => false, 'course' => true, 'site' => false);
    }

    function specialization() {
        if (!empty($this->config->blocktitle)){
        	$this->title = format_string($this->config->blocktitle);
        } else {
        	$this->title = '';
        }
    }

    function get_content() {
        global $THEME, $CFG, $COURSE, $USER, $DB;

        if ($this->content !== NULL) {
            return $this->content;
        }

        // fetch direct ascendants that are metas who point the current course as descendant
        // Admin sees all descendants
        if (@$this->config->checkenrollment && !has_capability('moodle/site:config', context_system::instance())){
	        $sql = "
	             SELECT DISTINCT 
	                c.id,
	                c.shortname,
	                c.fullname,
	                c.sortorder,
	                c.visible,
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
				if (!$descendant->catvisible && !has_capability('moodle/category:viewhiddencategories', $catcontext)){
					continue;
				}
           	
            	if ($categorymem != $descendant->catname){
            		$categorymem = $descendant->catname;
            		$this->content->items[] = '<b>'.format_string($descendant->catname).'</b>';
            	}

                // TODO : check visibility on course
                $context = context_course::instance($descendant->id);
                
                if ($descendant->visible || has_capability('moodle/course:viewhiddencourses', $context)){

                    $icon  = '';
                    $this->content->icons[] = $icon;
                    
                    if (!empty($this->config->stringlimit)){
	                    $fullname = shorten_text(format_string($descendant->fullname), 0 + @$this->config->stringlimit);
	                } else {
	                    $fullname = format_string($descendant->fullname);
	                }
    
    				$coursename = format_string($descendant->fullname);
                    $this->content->items[] = "<a title=\"" .s($coursename)."\" href=\"{$CFG->wwwroot}/course/view.php?id={$descendant->id}\">{$coursename}</a>";
                }
            }
        } else {
        	// if no descendants, make block invisible for everyone except when editing.
        	$this->title = '';
        }

        return $this->content;
    }

    /**
    *
    */
    function user_can_addto($page) {
        global $CFG, $COURSE;

		return true;

        $context = context_course::instance($COURSE->id);
        if (has_capability('block/course_descendants:addinstance', $context)){
        	return true;
        }
        return false;
    }

    /**
    *
    */
    function user_can_edit() {
        global $CFG, $COURSE;

        $context = context_course::instance($COURSE->id);
        
        if (has_capability('block/course_descendants:configure', $context)){
 	       return true;
        }

		return false;
    }	
}
