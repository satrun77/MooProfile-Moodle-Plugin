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
 * Displays user(s) profile information.
 *
 * @copyright  2011 Mohamed Alsharaf
 * @author     Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_mooprofile extends block_base
{

    protected $helper;
    protected $usersdisplayed;
    protected $displayfields = array('name', 'picture', 'email', 'sendmessage', 'phone1', 'phone2', 'lastaccess', 'isonline');

    public function init()
    {
        global $CFG;

        include_once realpath(dirname(__FILE__)) . '/locallib.php';
        $this->helper = new mooprofile_helper;
        $this->title = $this->helper->get_string('pluginname');
        $this->usersdisplayed = array();
    }

    /**
     * Display the content of a block
     *
     * @global moodle_database $DB
     * @return object
     */
    public function get_content()
    {
        if ($this->content !== NULL) {
            return $this->content;
        }

        // block visible for logged in users and if we have users or roles
        $hasusers = isset($this->config->user) && is_array($this->config->user);
        $hasroles = isset($this->config->role) && is_array($this->config->role);
        if (!isloggedin() || isguestuser() || (!$hasusers && !$hasroles)) {
            return '';
        }

        $this->content = new stdClass;
        $this->content->text = '<div class="mooprofileblock">';

        if ($this->config->message != '') {
            $this->content->text .= '<div class="desc">' . format_text($this->config->message, FORMAT_MOODLE) . '</div>';
        }

        // first display users based on roles in course page only
        if ($hasroles) {
            $this->render_roles();
        }

        // display users base on usernames in any page
        if ($hasusers) {
            $this->render_users();
        }

        $this->content->text .= '</div>';
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Render a list of users based on defined usernames
     *
     * @global moodle_database $DB
     * @return void
     */
    protected function render_users()
    {
        global $DB;

        $userscount = count($this->config->user);
        $i = 1;
        foreach ($this->config->user as $key => $username) {

            // skip a username for a profile displayed by the render_roles
            if (!isset($this->usersdisplayed[$username])) {
                $islast = ($i == $userscount) ? true : false;
                $user = $DB->get_record('user', array('username' => $username));
                if ($user) {
                    $this->content->text .= $this->render_user($user, $key, $islast);
                }
                unset($user);
            }

            $i++;
        }

        unset($this->usersdisplayed);
    }

    /**
     * Render a list of users based on their role in a course
     *
     * @global object $SITE
     * @global moodle_database $DB
     * @return void
     */
    protected function render_roles()
    {
        global $SITE;

        // this list only visible in a couse page
        if ($this->page->course->id == $SITE->id) {
            return;
        }

        $rolescount = count($this->config->role);
        $userscount = count($this->config->user);
        $i = 1;
        foreach ($this->config->role as $key => $roleid) {

            $islast = ($userscount == 0 && $i == $rolescount) ? true : false;
            $users = get_role_users($roleid, $this->page->context, false, 'u.*');
            foreach ($users as $user) {
                $this->content->text .= $this->render_user($user, $key, $islast);
                $this->usersdisplayed[$user->username] = $user;
            }
            unset($users);

            $i++;
        }
    }

    /**
     * Render a user block details
     *
     * @global core_renderer $OUTPUT
     * @global object $USER
     * @param object $user
     * @param int $key
     * @param boolean $islast
     * @return string
     */
    protected function render_user($user, $key, $islast = false)
    {
        global $OUTPUT, $USER, $CFG;

        $output = '<div class="mooprofile' . ($islast ? ' last' : '') . '" id="mooprofile-' . $user->id . '">';

        if ($this->can_display('picture', $key)) {
            $output .= '<div class="picture">';
            $output .= $OUTPUT->user_picture($user, array(
                'courseid' => $this->page->course->id,
                'size' => '100',
                'class' => 'profilepicture'));
            $output .= '</div>';
        }

        if ($this->can_display('name', $key)) {
            $output .= '<div class="fullname"><a href="' . $CFG->wwwroot . '/user/profile.php?id=' . $user->id . '">' . fullname($user) . '</a>';
            if ($this->can_display('isonline', $key)) {

                $timetoshowusers = 300;
                $timefrom = 100 * floor((time() - $timetoshowusers) / 100);
                if ($user->lastaccess > $timefrom) {
                    $output .= '<img src="' . $OUTPUT->pix_url('i/user') . '" alt="' . $this->helper->get_string('online') . '" title="' . $this->helper->get_string('online') . '"/>';
                }
            }
            $output .= '</div>';
        }

        // don't show email if user setting is hide email from everyone
        if ($this->can_display('email', $key) && $user->maildisplay != 0) {

            // if user setting  - allow everyone to see my email or
            // if only course member and current user in a course member
            $coursecontext = get_context_instance(CONTEXT_COURSE, $this->page->course->id);
            if ($user->maildisplay == 1 || ($user->maildisplay == 2 && is_enrolled($coursecontext, $USER, '', true))) {
                $output .= '<div class="email">';
                $output .= '<img src="' . $OUTPUT->pix_url('i/email') . '" alt="' . get_string('email') . '"/><span>' . obfuscate_mailto($user->email, '') . '<span>';
                if ($this->can_display('sendmessage', $key)) {
                    $output .= ' <span>(<a href="' . $CFG->wwwroot . '/message/index.php?id=' . $user->id . '" target="_blank">' . $this->helper->get_string('sendmessage') . '</a>)</span>';
                }
                $output .= '</div>';
            }
        } else if ($this->can_display('sendmessage', $key)) {
            $output .= '<div class="email">';
            $output .= '<img src="' . $OUTPUT->pix_url('i/email') . '" alt="' . get_string('email') . '"/>';
            $output .= '<span><a href="' . $CFG->wwwroot . '/message/index.php?id=' . $user->id . '" target="_blank">' . $this->helper->get_string('sendmessage') . '</a></span>';
            $output .= '</div>';
        }

        if ($this->can_display('phone1', $key) && $user->phone1 != '') {
            $output .= '<div class="phone">';
            $output .= '<img src="' . $OUTPUT->pix_url('i/feedback') . '" alt="' . get_string('phone') . '"/><strong>' . get_string('phone') . ':</strong><span>' . s($user->phone1) . '<span>';

            if ($this->can_display('phone2', $key) && $user->phone2 != '') {
                $output .= '<strong>' . $this->helper->get_string('or') . '</strong><span>' . s($user->phone2) . '<span>';
            }

            $output .= '</div>';
        }

        if ($this->can_display('lastaccess', $key) && $user->lastaccess != '') {
            $output .= '<div class="lastaccess">';
            $output .= '<strong>' . get_string('lastaccess') . '</strong><span>' . format_time($user->lastaccess) . '<span>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    /**
     * Checks if a configuration settings is enabled or not for a user
     *
     * @param int $key
     * @param string $name
     * @return boolean
     */
    protected function can_display($name, $key = 0)
    {
        if (!isset($this->config->{$name})) {
            return false;
        }

        $data = $this->config->{$name};
        if (!isset($data[$key]) || $data[$key] != 1) {
            return false;
        }

        return true;
    }

    /**
     * clean up the block config data from the empty usernames and zero roleid
     *
     * @return void
     */
    public function cleanup_blockdata($data = null)
    {
        if ($data == null) {
            $data = $this->config;
        }

        if (!isset($data->user) || !is_array($data->user)) {
            $data->user = array();
        }

        if (!isset($data->role) || !is_array($data->role)) {
            $data->role = array();
        }

        // remove empty usernames from config data
        $data->user = array_filter($data->user, function ($value) {
                    return !empty($value) || $value === 0;
                });

        // remove zero roleid from config data
        $data->role = array_filter($data->role, function ($value) {
                    return !empty($value) || $value === 0;
                });

        // remove un-used display fields and correct the arrays keys
        $users = $data->role + $data->user;
        $users2 = array_values($users);

        foreach ($this->displayfields as $field) {
            $newfield = array();
            foreach ($data->$field as $key => $value) {
                if (isset($users[$key])) {
                    $newfield[array_search($users[$key], $users2)] = $value;
                }
            }
            $data->$field = $newfield;
        }

        $newrole = array();
        foreach ($data->role as $key => $value) {
            $newrole[array_search($value, $users2)] = $value;
        }
        $data->role = $newrole;

        $newuser = array();
        foreach ($data->user as $key => $value) {
            $newuser[array_search($value, $users2)] = $value;
        }
        $data->user = $newuser;

        // update repeat element count
        $data->repeats = count($users);
    }

    public function instance_config_save($data, $nolongerused = false)
    {
        $this->cleanup_blockdata($data);

        return parent::instance_config_save($data, $nolongerused);
    }

    /**
     * allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config()
    {
        return false;
    }

    /**
     * allow more than one instance of the block on a page
     *
     * @return boolean
     */
    public function instance_allow_multiple()
    {
        return true;
    }

    /**
     * allow instances to have their own configuration
     *
     * @return boolean
     */
    public function instance_allow_config()
    {
        return true;
    }

    /**
     * instance specialisations (must have instance allow config true)
     *
     * @return void
     */
    public function specialization()
    {
        if (!empty($this->config->title)) {
            $this->title = strip_tags($this->config->title);
        }
    }

    /**
     * disable the displays of the instance configuration form (config_instance.html)
     *
     * @return boolean
     */
    public function instance_config_print()
    {
        return false;
    }

    /**
     * locations where block can be displayed
     *
     * @return array
     */
    public function applicable_formats()
    {
        return array('all' => true, 'mod' => true, 'tag' => true);
    }

    /**
     * post install configurations
     *
     */
    public function after_install()
    {
        
    }

    /**
     * post delete configurations
     *
     */
    public function before_delete()
    {
        
    }

}
