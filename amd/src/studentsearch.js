/**
 * @module     local_parentlink/studentsearch
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * Enables AJAX-powered student searching for the Parent Link Manager form.
 */
define(['jquery', 'core/notification'], function($, Notification) {

      /**
     * Initializes the student search field to perform AJAX lookups and
     * dynamically update the student listbox. This function is called by
     * Moodle's AMD loader when the page is ready.
     *
     * @function init
     * @returns {void}
     */
    function init() {
        const searchField = $('#id_studentsearch');
        const listbox = $('#id_studentids');

        if (M.cfg.developerdebug) {
            // eslint-disable-next-line no-console
            console.log('local_parentlink/studentsearch loaded (run "grunt amd" if stale)');
        }

        /**
         * Executes an AJAX search and merges new results into the listbox.
         */
        function runSearch() {
            const query = searchField.val().trim();
            if (!query.length) {
                return;
            }

            // ✅ Clear the search box immediately after initiating the search
            searchField.val('');

            // --- Show temporary loading indicator ---
            const loadingText = 'Loading...';
            const existingOptions = listbox.find('option').clone(true); // keep old ones
            const loadingOption = $('<option>', {
                text: loadingText,
                disabled: true,
                selected: true
            });
            listbox.empty().append(loadingOption);

            $.ajax({
                url: M.cfg.wwwroot + '/local/parentlink/ajax.php?sesskey=' + M.cfg.sesskey,
                method: 'GET',
                data: { term: query },
                dataType: 'json',
                success: function(results) {
                    listbox.empty(); // remove Loading...
                    // Reinsert previous results
                    existingOptions.each(function() {
                        listbox.append($(this));
                    });

                    if (!Array.isArray(results)) {
                        return;
                    }

                    const existingIds = new Set(listbox.find('option').map(function() {
                        return $(this).val();
                    }).get());

                    if (results.length === 0) {
                        Notification.addNotification({
                            message: 'No students found matching your search.',
                            type: 'warning'
                        });
                        return;
                    } else {
                        results.forEach(function(item) {
                            if (!existingIds.has(String(item.id))) {
                                listbox.append(
                                    $('<option>', { value: item.id, text: item.name })
                                );
                            }
                        });
                    }

                    listbox.trigger('change'); // Refresh enhanced widgets
                },
                error: function() {
                    Notification.addNotification({
                        message: 'An error occurred while searching.',
                        type: 'error'
                    });
                    return;
                }
            });
        }

        // Trigger search when pressing Enter in the search box.
        searchField.on('keydown', function(e) {
            if (e.key === 'Enter' || e.which === 13) {
                e.preventDefault();
                runSearch();
            }
        });

    }

    return { init: init };
});
