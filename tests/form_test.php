<?php

include_once __DIR__ . '/basetestcase.php';

/**
 * Unit tests for the block edit form class
 *
 * @group block_mooprofile
 */
class block_mooprofile_form_testcase extends block_mooprofile_basetestcase
{

    public function test_submission()
    {
        global $PAGE;

        $this->resetAfterTest(true);

        // Create course with 1 section.
        $this->create_course();

        // Mock block instance in a course
        $blockconfig = array(
            'title'   => 'Teachers',
            'user[0]' => 'teacher1',
            'user[1]' => 'teacher2',
            'repeats' => 2
        );

        $block = $this->mock_block_in_course($blockconfig);
        $this->mock_block_form_submit($blockconfig);

        // Create the block form and get the submitted data
        $form = new block_mooprofile_edit_form('', $block, $PAGE);
        $form->set_data($block->instance);
        $data = $form->get_data();

        // Assert form data
        $this->assertTrue($form->is_validated());
        $this->assertObjectHasAttribute('config_title', $data);
        $this->assertObjectHasAttribute('config_user', $data);
        $this->assertCount(2, $data->config_user);
        $this->assertEquals($data->config_user, array($block->config->{'user[0]'}, $block->config->{'user[1]'}));
    }

}
