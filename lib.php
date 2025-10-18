<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Generates a unique Moodle username based on a first and last name.
 *
 * This function:
 *  - Combines first and last name into "firstname.lastname"
 *  - Converts to lowercase
 *  - Replaces invalid characters with dots
 *  - Trims extra dots and shortens to 50 chars
 *  - Appends a number if needed to make the username unique
 *
 * Example:
 *     generate_unique_username('John', 'Smith') → 'john.smith'
 *     (if taken) → 'john.smith1', 'john.smith2', ...
 *
 * @param string $firstname The user's first name
 * @param string $lastname The user's last name
 * @return string A unique, valid username
 */
function local_parentlink_generate_unique_username(string $firstname, string $lastname): string {
    global $DB;

    // --- Combine and normalize names ---
    $combined = core_text::strtolower(trim($firstname . '.' . $lastname));

    // Replace any disallowed characters with dots.
    $base = preg_replace('/[^a-z0-9]+/', '.', $combined);

    // Remove leading/trailing dots and truncate to 50 chars.
    $base = trim($base, '.');
    $base = core_text::substr($base, 0, 50);

    if ($base === '') {
        $base = 'user';
    }

    // --- Ensure uniqueness ---
    $username = $base;
    $counter = 1;
    while ($DB->record_exists('user', ['username' => $username])) {
        $username = $base . $counter;
        $counter++;

        // Hard stop to prevent infinite loops on very common names.
        if ($counter > 999) {
            $username = $base . substr(uniqid(), 0, 6);
            break;
        }
    }

    return $username;
}
