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
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle. If not, see <http://www.gnu.org/licenses/>.


require_once('../../config.php');
require_once(dirname(__FILE__).'/create_form.php');
require_once($CFG->dirroot . '/mod/wiki/lib.php');
require_once($CFG->dirroot . '/mod/wiki/locallib.php');
require_once($CFG->dirroot . '/mod/wiki/pagelib.php');

// this page accepts two actions: new and create
// 'new' action will display a form contains page title and page format
// selections
// 'create' action will create a new page in db, and redirect to
// page editing page.
$action = optional_param('action', 'new', PARAM_TEXT);
// The title of the new page, can be empty
$title  = optional_param('title', '', PARAM_TEXT);
$swid   = optional_param('swid', 0, PARAM_INT);
$wid    = optional_param('wid', 0, PARAM_INT);
$gid    = optional_param('gid', 0, PARAM_INT);
$uid    = optional_param('uid', 0, PARAM_INT);

// 'create' action must be submitted by moodle form
// so sesskey must be checked
if ($action == 'create') {
    if (!confirm_sesskey()) {
        print_error('invalidsesskey');
    }
}

$swiki = null;

if (!empty($wid)) {
    // @TODO: Check for capabilities
    if (!$swid = wiki_add_subwiki($wid, $gid, $uid)) {
        print_error('invalidwikiid');
    }

}

if (!$subwiki = wiki_get_subwiki($swid)) {
    print_error('invalidswid', 'wiki');
}

if (!$wiki = wiki_get_wiki($subwiki->wikiid)) {
    print_error('invalidwikiid', 'wiki');
}

if (!$cm = get_coursemodule_from_instance('wiki', $wiki->id)) {
    print_error('invalidcoursemoduleid', 'wiki');
}

if (!$course = get_course_by_id($cm->course)) {
    print_error('invalidcourseid', 'wiki');
}

require_course_login($course->id, true, $cm);

add_to_log($course->id, 'createpage', 'createpage', 'view.php?id='.$cm->id, $wiki->id);

$wikipage = new page_wiki_create($wiki, $subwiki, $cm);

$wikipage->set_gid($gid);
$wikipage->set_swid($swid);
if (!empty($title)) {
    $wikipage->set_title($title);
} else {
    $wikipage->set_title(get_string('newpage', 'wiki'));
}

// set page action, and initialise moodle form
$wikipage->set_action($action);

switch ($action) {
case 'create':
    $wikipage->create_page($title);
    break;
case 'new':
    if (!empty($title)) {
        // create page from interlink with pagetitle
        $wikipage->print_header();
        $wikipage->print_content($title);
    } else {
        // create link from moodle navigation block without pagetitle
        $wikipage->print_header();
        // new page without page title
        $wikipage->print_content();
    }
    $wikipage->print_footer();
    break;
}