<?php
$string['pluginname'] = 'Parent Link Manager';
$string['manageparents'] = 'Manage Parent Accounts';
$string['searchheader'] = "Choose Student(s)";
$string['selectstudent'] = 'Select One or More Students';
$string['selectstudent_help'] = '<p>When a new parent account is created below, they will be connected to each of the students selected in this box.</p><p>Students in this list that are not selected will not be connected to the parent.</p>';
$string['searchstudents'] = 'Search for Student(s)';
$string['studentsearch'] = 'Search for Student(s)';
$string['studentsearch_help'] = '<p>To add students to the list box below:</p><ul><li>Type a full or partial first or last name</li><li>Press the ENTER key</li></ul><p>Any student with a matching name will be added below so that you can make your selections.</p>';
$string['addparent'] = 'Create New Parent Account';
$string['addparent_help'] = '<p>When you add a new parent account, the following actions will occur:</p><ul><li>A user with the username <em>first.last</em> will be created <em>(Numbers will be appended if not unique)</em></li><li>A parent role will be assigned for each student selected above</li><li>A password will be generated for the new user</li><li>You will be redirected to a template email that contains the username and default password for this new account</li></ul>';
$string['firstname'] = 'First name';
$string['lastname'] = 'Last name';
$string['email'] = 'Email address';
$string['generatedemail'] = 'Generated Email Message';
$string['copyemail'] = 'Copy to clipboard';
$string['noresults'] = 'No matching students found.';
$string['privacy:metadata'] = 'The Parent Link plugin does not store personal data beyond Moodle’s standard user tables.';
$string['setting_emailtemplate'] = 'Parent welcome email template';
$string['setting_emailtemplate_desc'] = 'You can use the following placeholders in the template:
<ul>
<li><code>[[siteurl]]</code> – Moodle root URL</li>
<li><code>[[username]]</code> – Parent account username</li>
<li><code>[[parent_email]]</code> – Parent email address</li>
<li><code>[[parent_first]]</code> – Parent first name</li>
<li><code>[[parent_last]]</code> – Parent last name</li>
<li><code>[[temppassword]]</code> – Temporary password</li>
<li><code>[[supportemail]]</code> – Moodle support email</li>
</ul>';

$string['backtoparentmanager'] = '← Back to Parent Manager';
$string['copiedtoclipboard'] = 'Copied to clipboard!';
$string['noemailfound'] = 'No email message found to display.';

$string['missingfirstname'] = 'First name is required.';
$string['missinglastname'] = 'Last name is required.';
$string['missingemail'] = 'Email address is required.';
$string['invalidemail'] = 'Please enter a valid email address.';
$string['parentcreatedsuccess'] = 'Parent account created successfully.';



