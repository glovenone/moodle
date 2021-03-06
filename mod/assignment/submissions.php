<?php

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir.'/plagiarismlib.php');

$id   = optional_param('id', 0, PARAM_INT);          // Course module ID
$a    = optional_param('a', 0, PARAM_INT);           // Assignment ID
$mode = optional_param('mode', 'all', PARAM_ALPHA);  // What mode are we in?
$download = optional_param('download' , 'none', PARAM_ALPHA); //ZIP download asked for?

$url = new moodle_url('/mod/assignment/submissions.php');
if ($id) {
    if (! $cm = get_coursemodule_from_id('assignment', $id)) {
        print_error('invalidcoursemodule');
    }

    if (! $assignment = $DB->get_record("assignment", array("id"=>$cm->instance))) {
        print_error('invalidid', 'assignment');
    }

    if (! $course = $DB->get_record("course", array("id"=>$assignment->course))) {
        print_error('coursemisconf', 'assignment');
    }
    $url->param('id', $id);
} else {
    if (!$assignment = $DB->get_record("assignment", array("id"=>$a))) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$assignment->course))) {
        print_error('coursemisconf', 'assignment');
    }
    if (! $cm = get_coursemodule_from_instance("assignment", $assignment->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    $url->param('a', $a);
}

if ($mode !== 'all') {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);
require_login($course->id, false, $cm);

require_capability('mod/assignment:grade', get_context_instance(CONTEXT_MODULE, $cm->id));

$PAGE->requires->js('/mod/assignment/assignment.js');
$PAGE->requires->js('/mod/assignment/download.js');

/// Load up the required assignment code
require($CFG->dirroot.'/mod/assignment/type/'.$assignment->assignmenttype.'/assignment.class.php');
$assignmentclass = 'assignment_'.$assignment->assignmenttype;
$assignmentinstance = new $assignmentclass($cm->id, $assignment, $cm, $course);

    $go = false;
    $get_feedback = false;
    $into_folder = false;
    
	  if(isset($_POST['submit_1'])){
   	     $into_folder = false;
   	     $go = true;   

   	  }
  	  else if(isset($_POST['submit_2'])){
   	     $into_folder = true;
   	     $go = true;

   	 }
   	 if(isset($_POST['feedback']) && $_POST['feedback'] == 'feedback'){
   	     $get_feedback = true;
    	     $go = true;

   	 }
   	 else{
    	     $get_feedback = false;
  	  }

if($download == "zip" && $go == true) {
    $assignmentinstance->download_submissions($get_feedback, $into_folder);
    $download = NULL;
    $assignmentinstance->submissions($mode);
} else {
    $assignmentinstance->submissions($mode);   // Display or process the submissions
}
