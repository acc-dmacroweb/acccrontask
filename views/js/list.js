/**
 * Script for cron tasks listing
 * Handles responsive functionality and action buttons
 */
(function($) {
    'use strict';

    // Global variables that will be defined from template
    var acccrontaskConfig = window.acccrontaskConfig || {
        baseUrl: '',
        token: ''
    };

    /**
     * Function to wrap table in responsive container
     */
    function wrapTable() {
        // Search for table in different possible locations
        var $table = $('.table').first();
        if (!$table.length) {
            $table = $('table').first();
        }
        
        if ($table.length && !$table.parent().hasClass('acccrontask-table-wrapper')) {
            $table.wrap('<div class="acccrontask-table-wrapper"></div>');
        }
    }

    /**
     * Processes table rows and replaces action buttons
     */
    function processTableRows() {
        $('.table tbody tr').each(function() {
            var $row = $(this);
            var $actions = $row.find('td:last-child');
            
            if ($actions.length) {
                var originalActionsHtml = $actions.html();
                var editLink = null;
                var deleteLink = null;
                var executeLink = null;
                
                // Search for all links in actions column
                $actions.find('a').each(function() {
                    var $link = $(this);
                    var href = $link.attr('href') || '';
                    var title = ($link.attr('title') || '').toLowerCase();
                    var text = $link.text().toLowerCase();
                    var onclick = ($link.attr('onclick') || '').toLowerCase();
                    var className = ($link.attr('class') || '').toLowerCase();
                    
                    // Identify link type
                    if (href.indexOf('update') !== -1 || href.indexOf('edit') !== -1 || 
                        href.indexOf('addacccrontask') !== -1 || 
                        title.indexOf('edit') !== -1 || title.indexOf('editar') !== -1 ||
                        text.indexOf('edit') !== -1 || text.indexOf('modificar') !== -1 ||
                        className.indexOf('edit') !== -1) {
                        editLink = href;
                    } else if (href.indexOf('delete') !== -1 || 
                              title.indexOf('delete') !== -1 || title.indexOf('eliminar') !== -1 ||
                              text.indexOf('delete') !== -1 || text.indexOf('eliminar') !== -1 ||
                              onclick.indexOf('delete') !== -1 ||
                              className.indexOf('delete') !== -1) {
                        deleteLink = href;
                    } else if (href.indexOf('executeNow') !== -1 || 
                              href.indexOf('execute') !== -1 ||
                              title.indexOf('execute') !== -1 || title.indexOf('ejecutar') !== -1 ||
                              text.indexOf('execute') !== -1 || text.indexOf('ejecutar') !== -1 ||
                              className.indexOf('execute') !== -1) {
                        executeLink = href;
                    }
                });
                
                // Also search in original HTML by patterns (more exhaustive)
                if (!deleteLink) {
                    var deleteMatches = [
                        originalActionsHtml.match(/href=["']([^"']*delete[^"']*)["']/i),
                        originalActionsHtml.match(/href=["']([^"']*deleteacccrontask[^"']*)["']/i),
                        originalActionsHtml.match(/deleteacccrontask[&?]id_acccrontask=(\d+)/i)
                    ];
                    
                    for (var i = 0; i < deleteMatches.length; i++) {
                        if (deleteMatches[i] && deleteMatches[i][1]) {
                            deleteLink = deleteMatches[i][1];
                            break;
                        }
                    }
                }
                
                // If we didn't find the links, try to build them from the ID
                var idMatch = originalActionsHtml.match(/id_acccrontask[=:](\d+)/i) || 
                             $row.find('input[type="checkbox"][name*="acccrontask"]').val() ||
                             originalActionsHtml.match(/\bid_acccrontask=(\d+)/i) ||
                             $row.find('td').first().text().match(/\d+/);
                
                var id = null;
                if (idMatch) {
                    id = idMatch[1] || (idMatch[0] ? idMatch[0].match(/\d+/)[0] : null);
                }
                
                // If we still don't have ID, search in entire row
                if (!id) {
                    var rowText = $row.text();
                    var idFromRow = rowText.match(/id_acccrontask[=:]?(\d+)/i);
                    if (idFromRow) {
                        id = idFromRow[1];
                    }
                }
                
                if (id) {
                    var baseUrl = acccrontaskConfig.baseUrl || '';
                    var token = acccrontaskConfig.token || '';
                    
                    if (!baseUrl || baseUrl === '') {
                        baseUrl = window.location.href.split('?')[0] + '?controller=AdminAccCronTask';
                    }
                    
                    if (!editLink) {
                        editLink = baseUrl + '&id_acccrontask=' + id + '&updateacccrontask&token=' + token;
                    }
                    // ALWAYS build deleteLink if we have ID
                    if (!deleteLink || deleteLink === '#') {
                        deleteLink = baseUrl + '&id_acccrontask=' + id + '&deleteacccrontask&token=' + token;
                    }
                    if (!executeLink) {
                        executeLink = baseUrl + '&id_acccrontask=' + id + '&executeNowacccrontask&token=' + token;
                    }
                }
                
                // Build new buttons with Bootstrap 5
                if (editLink || deleteLink || executeLink) {
                    var newActions = '<div class="acccrontask-actions">';
                    
                    // Edit button (Primary)
                    if (editLink) {
                        newActions += '<a href="' + editLink + '" class="btn btn-primary" title="Edit task">' +
                            '<i class="icon-edit"></i> <span>Edit</span>' +
                            '</a>';
                    }
                    
                    // Execute button (Success)
                    if (executeLink) {
                        newActions += '<a href="' + executeLink + '" class="btn btn-success" title="Execute task now" onclick="return confirm(\'Do you want to execute this task now?\');">' +
                            '<i class="icon-play"></i> <span>Execute</span>' +
                            '</a>';
                    }
                    
                    // Delete button (Danger) - ALWAYS show
                    if (deleteLink && deleteLink !== '#' && deleteLink.indexOf('delete') !== -1) {
                        newActions += '<a href="' + deleteLink + '" class="btn btn-danger" title="Delete task" onclick="return confirm(\'Are you sure you want to delete this task? This action cannot be undone.\');">' +
                            '<i class="icon-trash"></i> <span>Delete</span>' +
                            '</a>';
                    }
                    
                    newActions += '</div>';
                    $actions.html(newActions);
                }
            }
        });
    }

    /**
     * Initialization when document is ready
     */
    $(document).ready(function() {
        // Wrap table immediately and after delays
        wrapTable();
        setTimeout(wrapTable, 100);
        setTimeout(wrapTable, 500);
        
        // Process rows after a delay to ensure table is loaded
        setTimeout(processTableRows, 300);
    });

})(jQuery || window.$);

