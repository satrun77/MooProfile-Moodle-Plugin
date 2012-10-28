<?php

/**
 *
 * @package    block
 * @subpackage mooprofile
 * @copyright  2011 Mohamed Alsharaf
 * @author     Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Form for editing mooprofile block settings
 *
 * @package    block
 * @subpackage mooprofile
 * @copyright  2011 Mohamed Alsharaf
 * @author     Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_mooprofile_edit_form extends block_edit_form
{
    protected function specific_definition($mform)
    {
        global $CFG, $DB;

        include_once realpath(dirname(__FILE__)) . '/locallib.php';
        $helper = new mooprofile_helper();

        $mform->addElement('header', 'configheader', $helper->get_string('configheader_settings'));

        $mform->addElement('text', 'config_title', $helper->get_string('title'));
        $mform->setType('config_title', PARAM_MULTILANG);

        $mform->addElement('textarea', 'config_message', $helper->get_string('displaymessage'), array('rows' => '5', 'cols' => '60'));

        $repeatarray = array(
            $mform->createElement('header', 'headerconfiguser', $helper->get_string('configheader_user')),
            $mform->createElement('text', 'config_user', $helper->get_string('username')),
            $mform->createElement('selectyesno', 'config_name', $helper->get_string('displayname')),
            $mform->createElement('selectyesno', 'config_picture', $helper->get_string('displaypicture')),
            $mform->createElement('selectyesno', 'config_email', $helper->get_string('displayemail')),
            $mform->createElement('selectyesno', 'config_sendmessage', $helper->get_string('displaysendmessage')),
            $mform->createElement('selectyesno', 'config_phone1', $helper->get_string('displayphone1')),
            $mform->createElement('selectyesno', 'config_phone2', $helper->get_string('displayphone2')),
            $mform->createElement('selectyesno', 'config_lastaccess', $helper->get_string('displaylastaccess')),
            $mform->createElement('selectyesno', 'config_isonline', $helper->get_string('displayisonline')),
        );

        $repeatedoptions = array(
            'config_user' => array(
                'type' => PARAM_USERNAME
            ),
            'config_email' => array(
                'helpbutton' => array(
                    'displayemail', 'block_' . $helper->get_name()
                )
            ),
        );

        $repeatno = 1;
        if (isset($this->block->config->repeats)) {
            $repeatno = (int) $this->block->config->repeats;
        }
        $repeatno = $repeatno == 0 ? 1 : $repeatno;

        $this->repeat_elements($repeatarray, $repeatno, $repeatedoptions, 'config_repeats', 'config_add_fields', 1, $helper->get_string('addmoreusers'), false);
    }
}