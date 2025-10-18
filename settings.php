<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $ADMIN->add('accounts',
        new admin_externalpage(
            'local_parentlink',
            get_string('pluginname', 'local_parentlink'),
            new moodle_url('/local/parentlink/index.php'),
            'local/parentlink:manage'
        )
    );


    // --- Add main admin page under Plugins > Local ---
    $settings = new admin_settingpage(
        'local_parentlink_settings',
        get_string('pluginname', 'local_parentlink')
    );

    // --- Default email template ---
    $defaulttemplate = <<<EOT
Hello [[parent_first]],

A new parent or observer account has been created for you at [[siteurl]].

You can log in with the following details:
Username: [[username]]
Password: [[temppassword]]

When you log in for the first time, you will be required to create a new password.

If you have any problems, please contact [[supportemail]].

Sincerely,
The Moodle Administrator
EOT;

    $settings->add(new admin_setting_configtextarea(
        'local_parentlink/emailtemplate',
        get_string('setting_emailtemplate', 'local_parentlink'),
        get_string('setting_emailtemplate_desc', 'local_parentlink'),
        $defaulttemplate,
        PARAM_RAW,
        80,
        15
    ));

    // --- Register this page under Plugins -> Local ---
    $ADMIN->add('localplugins', $settings);
}

