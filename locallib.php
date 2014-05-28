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

include_once $CFG->libdir . '/form/text.php';

/**
 * Helper class for the block
 *
 * @copyright  2011 Mohamed Alsharaf
 * @author     Mohamed Alsharaf <mohamed.alsharaf@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mooprofile_helper
{
    private $name = 'mooprofile';

    /**
     * Return string from the block language
     * 
     * @param string $name
     * @param object $a
     */
    public function get_string($name, $a = null)
    {
        return get_string($name, 'block_' . $this->name, $a);
    }

    /**
     * Return block unique name
     * 
     * @return string
     */
    public function get_name()
    {
        return $this->name;
    }

    /**
     * Return the directory path of the block
     * 
     * @global object $CFG
     * @return string
     */
    public function get_dirpath()
    {
        global $CFG;
        return $CFG->dirroot . '/blocks/' . $this->name . '/';
    }

    /**
     * Get list of site roles names
     *
     * @param context $context
     * @param array $exclude
     * @return array
     */
    public function get_roles($context, $exclude = array())
    {
        $return = array();

        $roles = get_all_roles($context);
        $rolenames = role_fix_names($roles, $context, ROLENAME_ORIGINAL);
        foreach ($rolenames as $role) {
            if (!in_array($role->id, $exclude)) {
                $return[$role->id] = $role->localname;
            }
        }

        return $return;
    }
}
