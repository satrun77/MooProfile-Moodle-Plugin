<?php

global $CFG; // Needed for Moodle core code

include_once __DIR__ . '/../locallib.php';

/**
 * Unit tests for the helper class
 * 
 * @group block_mooprofile
 */
class block_mooprofile_helper_testcase extends advanced_testcase
{

    public function test_get_plugin_name()
    {
        $this->resetAfterTest(true);
        $helper = new mooprofile_helper;
        $this->assertEquals('mooprofile', $helper->get_name());
        $this->assertEquals('MooProfile Block', $helper->get_string('pluginname'));
    }

    public function test_get_roles()
    {
        $this->resetAfterTest(true);
        $helper = new mooprofile_helper;
        $roles = $helper->get_roles(context_system::instance());
        $this->assertGreaterThan(1, count($roles));
    }

}
