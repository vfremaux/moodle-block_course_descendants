<?php //$Id: block_course_descendants.php,v 1.1.2.1 2012/02/26 14:04:38 diml Exp $

class block_course_descendants extends block_list {
    function init() {
        $this->title = get_string('title', 'block_course_descendants');
        $this->version = 2012022000;
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
        	$this->title = filter_string($this->config->blocktitle);
        } else {
        	$this->title = '';
        }
    }

    function get_content() {
        global $THEME, $CFG, $COURSE, $USER;

        if ($this->content !== NULL) {
            return $this->content;
        }

        // fetch direct ascendants that are metas who point the current course as descendant
        // Admin sees all descendants
        if (@$this->config->checkenrollment && !has_capability('moodle/site:doanything', get_context_instance(CONTEXT_SYSTEM))){
	        $sql = "
	             SELECT DISTINCT 
	                c.id,
	                c.shortname,
	                c.fullname,
	                c.sortorder,
	                c.visible
	             FROM 
	                 {$CFG->prefix}course c,
	                 {$CFG->prefix}course_meta mc,
	                 {$CFG->prefix}context co,
	                 {$CFG->prefix}role_assignments ra
	             WHERE
	                c.id = mc.child_course AND
	                mc.parent_course = {$COURSE->id} AND
	                co.instanceid = c.id AND
	                co.contextlevel = ".CONTEXT_COURSE." AND
	                ra.contextid = co.id AND
	                ra.userid = {$USER->id}
	             ORDER BY
	                 c.sortorder
	        ";
	    } else {
	        $sql = "
	             SELECT DISTINCT 
	                c.id,
	                c.shortname,
	                c.fullname,
	                c.sortorder,
	                c.visible
	             FROM 
	                 {$CFG->prefix}course c,
	                 {$CFG->prefix}course_meta mc
	             WHERE
	                c.id = mc.child_course AND
	                mc.parent_course = {$COURSE->id}
	             ORDER BY
	                 c.sortorder
	        ";
	    }

        $descendants = get_records_sql($sql);
        
        $this->content = new stdClass;
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';
        
        if ($descendants) {
            foreach ($descendants as $descendant) {

                // TODO : check visibility on upper categories
                $context = get_context_instance(CONTEXT_COURSE, $descendant->id);
                
                if ($descendant->visible || has_capability('moodle/course:viewhiddencourses', $context)){

                    $icon  = '';
                    $this->content->icons[] = $icon;
                    
                    if (!empty($this->config->stringlimit)){
	                    $fullname = shorten_text($descendant->fullname, 0 + @$this->config->stringlimit);
	                } else {
	                    $fullname = $descendant->fullname;
	                }
    
                    $this->content->items[]="<a title=\"" .s($descendant->fullname).
                        "\" href=\"{$CFG->wwwroot}/course/view.php?id={$descendant->id}\">{$fullname}</a>";
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

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        if (has_capability('block/course_descendants:canaddto', $context)){
        	return true;
        }
        return false;
    }

    /**
    *
    */
    function user_can_edit() {
        global $CFG, $COURSE;

        $context = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        
        if (has_capability('block/course_descendants:configure', $context)){
 	       return true;
        }

		return false;
    }
	
}

?>
