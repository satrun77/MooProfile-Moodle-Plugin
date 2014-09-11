<?php

global $CFG; // Needed for Moodle core code

include_once __DIR__ . '/../locallib.php';
require_once $CFG->dirroot . '/blocks/edit_form.php';
include_once __DIR__ . '/../edit_form.php';

/**
 * block_mooprofile_basetestcase contains shared & helper methods for the block tests cases
 *
 */
abstract class block_mooprofile_basetestcase extends advanced_testcase
{
    /**
     * Course object
     *
     * @var object
     */
    protected $course;

    /**
     * Helper object
     *
     * @var mooprofile_helper
     */
    protected $helper;

    /**
     * Teacher role object
     *
     * @var object
     */
    protected $teacherrole;

    /**
     * An array of teachers objects
     *
     * @var array
     */
    protected $teachers;

    public function setUp()
    {
        parent::setUp();

        $this->resetAfterTest(true);

        // Helper class
        $this->helper = new mooprofile_helper();
    }

    /**
     * Enrol some users in the current course
     *
     * @global moodle_database $DB
     */
    protected function enrol_users_in_course()
    {
        global $DB;

        // Create and enroll 1 teacher and 1 student
        $this->teacherrole = $DB->get_record('role', array('shortname' => 'editingteacher'));

        $date = new DateTime();
        $date->modify('+4 minutes');
        $teacher1 = $this->getDataGenerator()->create_user(array(
            'phone2'     => '06 1234456',
            'lastaccess' => $date->getTimestamp(),
            'firstname'  => 'Teacher',
            'lastname'   => '1',
        ));
        $teacher2 = $this->getDataGenerator()->create_user(array(
            'firstname' => 'Teacher',
            'lastname'  => '2',
        ));
        $this->getDataGenerator()->enrol_user($teacher1->id, $this->course->id, $this->teacherrole->id);
        $this->getDataGenerator()->enrol_user($teacher2->id, $this->course->id, $this->teacherrole->id);

        $this->teachers = array($teacher1->username => $teacher1, $teacher2->username => $teacher2);
    }

    /**
     * Add block to current page using the block manager
     *
     * @param  array            $config
     * @return block_mooprofile
     */
    protected function add_block(array $config = array())
    {
        // Block name and location in page
        $regionname = 'a-region';

        // Mock course page from current course
        $page = $this->mock_course_page();

        // Block mananger
        $blockmanager = $this->get_block_manager($page, array($regionname));

        // Add the block to the top of the region
        $blockmanager->add_block($this->helper->get_name(), $regionname, 0, false);
        $blockmanager->load_blocks();

        // Get all blocks in the region. The first is our block
        $blocks = $blockmanager->get_blocks_for_region($regionname);
        $block = $blocks[0];

        // Set & save the block config data
        $statuses = array_fill(0, count($this->teachers), 1);
        $displayfields = array_fill_keys($block->get_displayfields(), $statuses);

        $block->config = (object) array_merge($displayfields, $config);
        $block->instance_config_commit();

        return $block;
    }

    /**
     * Mock current course page
     *
     * @return \moodle_page
     */
    protected function mock_course_page()
    {
        global $PAGE;

        // Make sure we have a course
        $this->create_course();

        $context = context_course::instance($this->course->id);
        $PAGE->set_context($context);
        $PAGE->set_pagetype('page-type');
        $PAGE->set_subpage('');
        $PAGE->set_course($this->course);

        return $PAGE;
    }

    /**
     * Get block manager for a page with regions.
     *
     * @return \block_manager
     */
    protected function get_block_manager(\moodle_page $page, $regions)
    {
        $blockmanager = new block_manager($page);
        $blockmanager->add_regions($regions, false);
        $blockmanager->set_default_region($regions[0]);

        return $blockmanager;
    }

    /**
     * Assert that a block content displays all data
     *
     * @param object $teacher
     * @param string $body
     */
    protected function assertAllVisible($teacher, $body)
    {
        $fullname = fullname($teacher);
        $this->assertContainsProfileLink($teacher, $body);
        $this->assertContainsMessage($teacher, $body);
        $this->assertContainsName($fullname, $body);
        $this->assertContainsPicture($fullname, $body);
        $this->assertContainsEmail($teacher, $body);
        $this->assertContainsLastAccess($body);
        $this->assertContainsIsOnline($body);
        $this->assertContainsPhone2($teacher, $body);
    }

    /**
     * Assert the profile link exists in the block content
     *
     * @param object  $teacher
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsProfileLink($teacher, $body, $inverse = false)
    {
        $this->assert_contains('profile.php?id=' . $teacher->id, $body, $inverse);
    }

    /**
     * Assert the message link exists in block content
     *
     * @param object  $teacher
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsMessage($teacher, $body, $inverse = false)
    {
        $this->assert_contains('/message/index.php?id=' . $teacher->id, $body, $inverse);
    }

    /**
     * Assert the full name of the user exists in the block content
     *
     * @param string  $fullname
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsName($fullname, $body, $inverse = false)
    {
        $this->assert_contains('>' . $fullname . '<', $body, $inverse);
    }

    /**
     * Assert the picture of the user exists in the block content
     *
     * @param string  $fullname
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsPicture($fullname, $body, $inverse = false)
    {
        $this->assert_contains(get_string('pictureof', '', $fullname), $body, $inverse);
    }

    /**
     * Assert the email of the user exists in the block content
     *
     * @param object  $teacher
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsEmail($teacher, $body, $inverse = false)
    {
        $this->assert_contains($teacher->email, $body, $inverse);
    }

    /**
     * Assert the last access details of the user exists in the block content
     *
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsLastAccess($body, $inverse = false)
    {
        $this->assert_contains(get_string('lastaccess'), $body, $inverse);
    }

    /**
     * Assert the online status of the user exists in the block content
     *
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsIsOnline($body, $inverse = false)
    {
        $this->assert_contains($this->helper->get_string('online'), $body, $inverse);
    }

    /**
     * Assert the second phone number of the user exists in the block content
     *
     * @param object  $teacher
     * @param string  $body
     * @param boolean $inverse
     */
    protected function assertContainsPhone2($teacher, $body, $inverse = false)
    {
        $this->assert_contains($teacher->phone2, $body, $inverse);
    }

    /**
     * Helper method to call assertContains or assertNotContains
     *
     * @param string  $needle
     * @param string  $haystack
     * @param boolean $inverse
     */
    private function assert_contains($needle, $haystack, $inverse = false)
    {
        $method = !$inverse ? 'assertContains' : 'assertNotContains';
        $this->$method($needle, $haystack);
    }

    /**
     * Create a dummy course
     *
     * @return object
     */
    protected function create_course()
    {
        if (null == $this->course) {
            $this->course = $this->getDataGenerator()->create_course(array(
                'shortname'   => 'TestC1',
                'fullname'    => 'Test Course 1',
                'numsections' => 1), array('createsections' => true));
        }

        return $this->course;
    }

    /**
     * Mock the existing of a block in a course
     *
     * @param  object           $course
     * @param  array            $config
     * @return block_mooprofile
     */
    protected function mock_block_in_course(array $config)
    {
        $context = context_course::instance($this->course->id);

        $blockinstance = new \stdClass();
        $blockinstance->id = 1;
        $blockinstance->blockname = 'mooprofile';
        $blockinstance->parentcontextid = $context->id;
        $blockinstance->showinsubcontexts = true;
        $blockinstance->pagetypepattern = 'course-view-*';
        $blockinstance->subpagepattern = '';
        $blockinstance->region = "side-post";
        $blockinstance->defaultregion = "side-post";
        $blockinstance->defaultweight = 1;
        $blockinstance->weight = 1;
        $blockinstance->configdata = '';
        $blockinstance->visible = 1;

        $block = block_instance('mooprofile', $blockinstance);
        $block->config = new stdClass();

        // Convert array field names 'user[0]' into a property with array value, else create property with value
        foreach ($config as $name => $value) {
            if (strpos($name, '[') !== false && preg_match('/^(\w+)\[(\d+)\]$/', $name, $matches) !== false) {
                list($name, $fieldname, $fieldindex) = $matches;
                if (!empty($fieldname) && $fieldindex >= 0) {
                    $block->config->{$fieldname}[$fieldindex] = $value;
                }
            }
            $block->config->$name = $value;
        }

        return $block;
    }

    /**
     * Mock form submission for block edit form
     *
     * @param array $config
     */
    protected function mock_block_form_submit(array $config)
    {
        $formdata = array();

        // Prefix field names with config_
        foreach ($config as $name => $value) {
            $formdata['config_' . $name] = $value;
        }

        block_mooprofile_edit_form::mock_submit($formdata);
    }

}
