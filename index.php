<?php
require_once('../../config.php');

require_login();
require_capability('local/parentlink:manage', context_system::instance());

global $DB, $CFG, $PAGE, $OUTPUT, $USER, $SITE;

$PAGE->set_url(new moodle_url('/local/parentlink/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_parentlink'));

require_once(__DIR__ . '/classes/form/addparent_form.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once(__DIR__ . '/lib.php');


$search = optional_param('studentsearch', '', PARAM_RAW);
$previousids = (array)optional_param('studentids', [], PARAM_INT);
$students = [];

// Rebuild existing selected students so they stay visible.
if (!empty($previousids)) {
    list($insql, $inparams) = $DB->get_in_or_equal($previousids, SQL_PARAMS_QM);
    $existing = $DB->get_records_sql_menu("
        SELECT id, CONCAT(firstname, ' ', lastname) AS fullname
          FROM {user}
         WHERE id $insql", $inparams);
    $students = $existing;
}

// Add search results (merge without overwriting existing ones).
if (!empty($search)) {
    $searchlike = '%' . $DB->sql_like_escape($search) . '%';
    $found = $DB->get_records_sql_menu("
        SELECT id, CONCAT(firstname, ' ', lastname) AS fullname
          FROM {user}
         WHERE deleted = 0
           AND id IN (SELECT userid FROM {role_assignments} WHERE roleid = ?)
           AND (firstname LIKE ? OR lastname LIKE ?)",
        [5, $searchlike, $searchlike]);

    $students = $students + $found; // merge arrays, preserving previous ones
}


$mform = new \local_parentlink\form\addparent_form(null, ['students' => $students]);

$emailtemplate = '';

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/parentlink/index.php'));
}

if ($data = $mform->get_data()) {
    $studentids = $data->studentids ?? [];
    $errors = [];

    // --- Validate required fields ---
    if (empty($data->firstname)) {
        $errors[] = get_string('missingfirstname', 'local_parentlink');
    }

    if (empty($data->lastname)) {
        $errors[] = get_string('missinglastname', 'local_parentlink');
    }

    if (empty($data->email)) {
        $errors[] = get_string('missingemail', 'local_parentlink');
    } else if (!validate_email($data->email)) {
        $errors[] = get_string('invalidemail', 'local_parentlink');
    }

    // --- If validation fails, show notifications ---
    if (!empty($errors)) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_parentlink'));
        foreach ($errors as $msg) {
            echo $OUTPUT->notification($msg, \core\output\notification::NOTIFY_ERROR);
        }
        $mform->display();
        echo $OUTPUT->footer();
        exit;
    }

    // --- Proceed only if students and all fields are valid ---
    if (!empty($studentids)) {


        // Check if a parent user with this email already exists.
        if ($DB->record_exists('user', ['email' => $data->email])) {
            redirect(
                new moodle_url('/local/parentlink/index.php'),
                'A user with this email already exists. No duplicate created.',
                5,
                \core\output\notification::NOTIFY_WARNING
            );
        }

        // Create new user account.
        $user = new stdClass();
        $user->auth = 'manual';
        $user->confirmed = 1;
        $user->mnethostid = $CFG->mnet_localhost_id;
        $user->username = local_parentlink_generate_unique_username($data->firstname, $data->lastname);
        $user->email = $data->email;
        $user->firstname = $data->firstname;
        $user->lastname = $data->lastname;
        $user->policyagreed = 1;
        $user->timecreated = time();
        $user->timemodified = time();
        $temppass = generate_password(8);
        $user->password = hash_internal_user_password($temppass);
        $user->forcepasswordchange = 1;

        // Begin a database transaction
        $transaction = $DB->start_delegated_transaction();

        // create a new Moodle user in this transaction
        $userid = user_create_user($user, true, true);

        print_object($userid);

        // Assign parent role to all selected students.
        $parentrole = $DB->get_record('role', ['shortname' => 'parent'], '*', MUST_EXIST);
        foreach ($studentids as $sid) {
            $context = context_user::instance($sid);
            // Avoid duplicate assignment if it already exists.
            if (!$DB->record_exists('role_assignments', [
                'roleid' => $parentrole->id,
                'userid' => $userid,
                'contextid' => $context->id
            ])) {
                role_assign($parentrole->id, $userid, $context->id);               
            }
        }

        print_object($transaction);
        // Commit transaction after user and roles are fully assigned
        $transaction->allow_commit();

        // Generate email template.
        $support = core_user::get_support_user();
        
        // Get custom email template
        $template = get_config('local_parentlink', 'emailtemplate');

        $replacements = [
            '[[siteurl]]' => $CFG->wwwroot,
            '[[username]]' => $user->username,
            '[[parent_email]]' => $user->email,
            '[[parent_first]]' => $user->firstname,
            '[[parent_last]]' => $user->lastname,
            '[[temppassword]]' => $temppass,
            '[[supportemail]]' => $support->email,
        ];

        $emailtemplate = str_replace(array_keys($replacements), array_values($replacements), $template);

        // ✅ Store email template in session (avoids large GET params).
        $SESSION->parentlink_emailtemplate = $emailtemplate;

        // ✅ Redirect to the email page to display it.
        redirect(
            new moodle_url('/local/parentlink/email.php'),
            get_string('parentcreatedsuccess', 'local_parentlink'),
            0,
            \core\output\notification::NOTIFY_SUCCESS
        );



    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_parentlink'));
$mform->display();

if ($emailtemplate) {
    echo html_writer::tag('h4', get_string('generatedemail', 'local_parentlink'));
    echo html_writer::tag('textarea', $emailtemplate, [
        'readonly' => 'readonly',
        'rows' => 10,
        'cols' => 80,
        'id' => 'emailtemplate',
        'class' => 'form-control mb-2'
    ]);
    echo html_writer::tag('button', get_string('copyemail', 'local_parentlink'),
        ['class' => 'btn btn-secondary', 'id' => 'copybtn']);
    $PAGE->requires->js_init_code("
        document.getElementById('copybtn').addEventListener('click', function() {
            const t = document.getElementById('emailtemplate');
            t.select();
            navigator.clipboard.writeText(t.value);
        });
    ");
}

$PAGE->requires->js_call_amd('local_parentlink/studentsearch', 'init');

echo $OUTPUT->footer();
