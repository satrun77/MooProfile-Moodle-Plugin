<?php

include_once __DIR__ . '/basetestcase.php';

/**
 * Unit tests for the main block class
 *
 * @group block_mooprofile
 */
class block_mooprofile_block_testcase extends block_mooprofile_basetestcase
{

    public function test_render_by_usernames()
    {
        global $USER;

        // Create course with 1 section.
        $this->create_course();

        // Enrollments
        $this->enrol_users_in_course();

        // Login as the teacher!
        $USER = current($this->teachers);

        // Add block
        $block = $this->add_block(array(
            'title'   => 'Teacher',
            'message' => 'Teacher of the course.',
            'user'    => array_keys($this->teachers),
            'name'    => array(
                1, 0
            ),
            'picture' => array(
                1, 0
            ),
            'email'   => array(
                1, 0
            )
        ));

        // Render the block content
        $content = $block->get_content();
        $body = html_entity_decode($content->text);

        // Assert
        $this->assertAllVisible(current($this->teachers), $body);

        $teacher = next($this->teachers);
        $fullname = fullname($teacher);
        $this->assertContainsName($fullname, $body, true);
        $this->assertContainsPicture($fullname, $body, true);
        $this->assertContainsEmail($teacher, $body, true);
        $this->assertContainsMessage($teacher, $body);
    }

    public function test_render_by_role()
    {
        global $USER;

        // Create course with 1 section.
        $this->create_course();

        // Enrollments
        $this->enrol_users_in_course();

        // Login as the teacher!
        $USER = current($this->teachers);

        // Add block
        $block = $this->add_block(array(
            'title'   => 'Teacher',
            'message' => 'Teachers of the course.',
            'role'    => array(
                $this->teacherrole->id,
            ),
        ));

        // Render the block content
        $content = $block->get_content();
        $body = html_entity_decode($content->text);
        $teacher = current($this->teachers);

        $this->assertAllVisible($teacher, $body);
        $this->assertEquals($content, $block->get_content());
    }

    public function test_render_empty_block()
    {
        global $USER;

        // Create course with 1 section.
        $this->create_course();

        // Enrollments
        $this->enrol_users_in_course();

        // Add block
        $block = $this->add_block(array(
            'title'   => 'Teacher',
            'message' => 'Teacher of the course.',
        ));

        // Assert content
        $this->assertEmpty($block->get_content());

        // Login as the teacher!
        $USER = current($this->teachers);

        // Remove roles & users
        unset($block->config->role);
        unset($block->config->user);

        // Assert content
        $this->assertEmpty($block->get_content());
    }

    public function test_static_methods()
    {
        $block = new block_mooprofile();

        $this->assertFalse($block->has_config());
        $this->assertTrue($block->instance_allow_multiple());
        $this->assertTrue($block->instance_allow_config());
        $this->assertFalse($block->instance_config_print());
        $formats = $block->applicable_formats();
        foreach ($formats as $key => $value) {
            $this->assertTrue($value);
            $this->assertTrue(in_array($key, array('all', 'mod', 'tag')));
        }
        $this->assertEmpty($block->after_install());
        $this->assertEmpty($block->before_delete());

        $customtitle = 'Test title';
        $block->config = new stdClass();
        $block->title = $customtitle;
        $block->specialization();
        $this->assertEquals($block->title, $customtitle);
    }

}
