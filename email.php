<?php
/**
 * Displays the generated parent welcome email.
 *
 * @package   local_parentlink
 */

require_once('../../config.php');
require_login();
require_capability('local/parentlink:manage', context_system::instance());

global $PAGE, $OUTPUT, $SESSION, $SITE;

$PAGE->set_url(new moodle_url('/local/parentlink/email.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_parentlink'));
$PAGE->set_heading(format_string($SITE->fullname));

// Retrieve and clear email template from session.
$emailtemplate = $SESSION->parentlink_emailtemplate ?? '';
unset($SESSION->parentlink_emailtemplate);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('generatedemail', 'local_parentlink'));

if (!empty($emailtemplate)) {
    echo html_writer::start_div('card shadow-sm p-3 mt-3');
    echo html_writer::tag('textarea', $emailtemplate, [
        'readonly' => 'readonly',
        'rows' => 12,
        'cols' => 80,
        'id' => 'emailtemplate',
        'class' => 'form-control mb-3'
    ]);

    echo html_writer::tag('button', get_string('copyemail', 'local_parentlink'), [
        'class' => 'btn btn-secondary me-2',
        'id' => 'copybtn'
    ]);

    echo html_writer::link(
        new moodle_url('/local/parentlink/index.php'),
        get_string('backtoparentmanager', 'local_parentlink'),
        ['class' => 'btn btn-link']
    );

    echo html_writer::end_div();

    // JS: Copy-to-clipboard with Moodle notification.
    $PAGE->requires->js_init_code("
        const btn = document.getElementById('copybtn');
        if (btn) {
            btn.addEventListener('click', () => {
                const ta = document.getElementById('emailtemplate');
                ta.select();
                navigator.clipboard.writeText(ta.value).then(() => {
                    require(['core/notification'], function(Notification) {
                        Notification.addNotification({
                            message: '".get_string('copiedtoclipboard', 'local_parentlink')."',
                            type: 'success'
                        });
                    });
                });
            });
        }
    ");
} else {
    echo $OUTPUT->notification(get_string('noemailfound', 'local_parentlink'), 'warning');
    echo html_writer::link(
        new moodle_url('/local/parentlink/index.php'),
        get_string('backtoparentmanager', 'local_parentlink'),
        ['class' => 'btn btn-link mt-3']
    );
}

echo $OUTPUT->footer();
