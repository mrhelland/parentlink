<?php
require_once('../../config.php');
require_login();
require_capability('local/parentlink:manage', context_system::instance());
//require_sesskey();


$term = optional_param('term', '', PARAM_RAW_TRIMMED);

$results = [];
if ($term !== '') {
    global $DB;
    $like = '%' . $DB->sql_like_escape($term) . '%';
    $sql = "SELECT id, CONCAT(firstname, ' ', lastname) AS fullname
              FROM {user}
             WHERE deleted = 0
               AND id IN (SELECT userid FROM {role_assignments} WHERE roleid = ?)
               AND (firstname LIKE ? OR lastname LIKE ?)
             ORDER BY lastname ASC, firstname ASC";
    $records = $DB->get_records_sql($sql, [5, $like, $like]);
    foreach ($records as $r) {
        $results[] = ['id' => $r->id, 'name' => $r->fullname];
    }
}

echo json_encode($results);
die;
