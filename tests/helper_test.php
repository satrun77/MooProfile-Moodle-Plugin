<?php

include_once __DIR__ . '/basetestcase.php';

/**
 * Unit tests for the helper class
 *
 * @group block_mooprofile
 */
class block_mooprofile_helper_testcase extends block_mooprofile_basetestcase
{

    public function test_get_plugin_name()
    {
        $this->resetAfterTest(true);
        $helper = new mooprofile_helper();
        $this->assertEquals('mooprofile', $helper->get_name());
        $this->assertEquals('MooProfile Block', $helper->get_string('pluginname'));
    }

    public function test_get_roles()
    {
        $this->resetAfterTest(true);
        $helper = new mooprofile_helper();
        $roles = $helper->get_roles(context_system::instance());
        $this->assertGreaterThan(1, count($roles));
    }

    public function test_get_dirpath()
    {
        $this->resetAfterTest(true);
        $helper = new mooprofile_helper();
        $this->assertEquals(realpath(__DIR__ . '/../') . '/', $helper->get_dirpath());
    }

}
