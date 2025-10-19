<?php
namespace local_parentlink\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for adding a new parent user and linking to one or more students.
 */
class addparent_form extends \moodleform {

    public function definition() {
        $mform = $this->_form;

        // === Search section ===
        $mform->addElement('header', 'searchhdr', get_string('searchheader', 'local_parentlink'));

        $mform->addElement('text', 'studentsearch', get_string('studentsearch', 'local_parentlink'));
        $mform->addHelpButton('studentsearch', 'studentsearch', 'local_parentlink');
        $mform->setType('studentsearch', PARAM_TEXT);


        // === Student selection section ===
        //$mform->addElement('header', 'studenthdr', get_string('selectstudent', 'local_parentlink'));

        $students = $this->_customdata['students'] ?? [];

        // Use size + style attributes to ensure visible width even when empty.
        $select = $mform->addElement(
            'select',
            'studentids',
            get_string('selectstudent', 'local_parentlink'),
            $students,
            [
                'size' => 8,
                'style' => 'min-width:400px;width:100%;max-width:600px;',
            ]
        );
        $select->setMultiple(true);
        $mform->setType('studentids', PARAM_INT);
        $mform->addHelpButton('studentids', 'selectstudent', 'local_parentlink');

        // === Parent details section ===
        $mform->addElement('header', 'parenthdr', get_string('addparent', 'local_parentlink'));
        $mform->addHelpButton('parenthdr', 'addparent', 'local_parentlink');

        $mform->addElement('text', 'firstname', get_string('firstname', 'local_parentlink'));
        $mform->setType('firstname', PARAM_NOTAGS);

        $mform->addElement('text', 'lastname', get_string('lastname', 'local_parentlink'));
        $mform->setType('lastname', PARAM_NOTAGS);

        $mform->addElement('text', 'email', get_string('email', 'local_parentlink'));
        $mform->setType('email', PARAM_EMAIL);

        // === Action buttons ===
        $this->add_action_buttons(true, get_string('addparent', 'local_parentlink'));

    }
}
