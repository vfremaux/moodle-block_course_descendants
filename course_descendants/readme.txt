Course descendants block
Release : 02/2012
Version : 1.9
Author : Valery Fremaux (valery.fremaux@gmail.com)
################################

Course descendants block allows organising backpath navigation
from a meta course to a master course that provids its enrolments.

The block will provide users with links to all courses that
use the current course as metacourse.

An option can be setup to force the block to check the enrolment
status of the user in the descendant course, before printing the
navigation link.

As per course_ascendants, in the default configuration, only administrators
can add or setup this block within a course, as the block may provide unchecked accesses
if configured by unauthorized people. 

# Install
###################

Simple usual installation in /blocks, then browsing to notifications

# Patchs required
####################

No patches required, no core changes

# Related
#########################

Block course_descendants is related to course_ascendants that provides
a "subcourse" summary and a simple way to open/close subchapters (metacourses).