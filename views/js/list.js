/**
<<<<<<< HEAD
 * Script for cron tasks listing
 * Handles responsive functionality and action buttons
=======
 * Script para el listado de tareas cron
 * Maneja la funcionalidad responsive y los botones de acción
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
 */
(function($) {
    'use strict';

<<<<<<< HEAD
    // Global variables that will be defined from template
=======
    // Variables globales que se definirán desde el template
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
    var acccrontaskConfig = window.acccrontaskConfig || {
        baseUrl: '',
        token: ''
    };

    /**
<<<<<<< HEAD
     * Function to wrap table in responsive container
     */
    function wrapTable() {
        // Search for table in different possible locations
=======
     * Función para envolver la tabla en un contenedor responsive
     */
    function wrapTable() {
        // Buscar la tabla en diferentes ubicaciones posibles
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
        var $table = $('.table').first();
        if (!$table.length) {
            $table = $('table').first();
        }
        
        if ($table.length && !$table.parent().hasClass('acccrontask-table-wrapper')) {
            $table.wrap('<div class="acccrontask-table-wrapper"></div>');
        }
    }

    /**
<<<<<<< HEAD
     * Processes table rows and replaces action buttons
=======
     * Procesa las filas de la tabla y reemplaza los botones de acción
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
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
                
<<<<<<< HEAD
                // Search for all links in actions column
=======
                // Buscar todos los enlaces en la columna de acciones
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
                $actions.find('a').each(function() {
                    var $link = $(this);
                    var href = $link.attr('href') || '';
                    var title = ($link.attr('title') || '').toLowerCase();
                    var text = $link.text().toLowerCase();
                    var onclick = ($link.attr('onclick') || '').toLowerCase();
                    var className = ($link.attr('class') || '').toLowerCase();
                    
<<<<<<< HEAD
                    // Identify link type
=======
                    // Identificar tipo de enlace
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
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
                
<<<<<<< HEAD
                // Also search in original HTML by patterns (more exhaustive)
=======
                // Buscar también en el HTML original por patrones (más exhaustivo)
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
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
                
<<<<<<< HEAD
                // If we didn't find the links, try to build them from the ID
=======
                // Si no encontramos los enlaces, intentar construirlos desde el ID
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
                var idMatch = originalActionsHtml.match(/id_acccrontask[=:](\d+)/i) || 
                             $row.find('input[type="checkbox"][name*="acccrontask"]').val() ||
                             originalActionsHtml.match(/\bid_acccrontask=(\d+)/i) ||
                             $row.find('td').first().text().match(/\d+/);
                
                var id = null;
                if (idMatch) {
                    id = idMatch[1] || (idMatch[0] ? idMatch[0].match(/\d+/)[0] : null);
                }
                
<<<<<<< HEAD
                // If we still don't have ID, search in entire row
=======
                // Si aún no tenemos ID, buscar en toda la fila
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
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
<<<<<<< HEAD
                    // ALWAYS build deleteLink if we have ID
=======
                    // SIEMPRE construir deleteLink si tenemos ID
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
                    if (!deleteLink || deleteLink === '#') {
                        deleteLink = baseUrl + '&id_acccrontask=' + id + '&deleteacccrontask&token=' + token;
                    }
                    if (!executeLink) {
                        executeLink = baseUrl + '&id_acccrontask=' + id + '&executeNowacccrontask&token=' + token;
                    }
                }
                
<<<<<<< HEAD
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
=======
                // Construir los nuevos botones con Bootstrap 5
                if (editLink || deleteLink || executeLink) {
                    var newActions = '<div class="acccrontask-actions">';
                    
                    // Botón Modificar (Primary)
                    if (editLink) {
                        newActions += '<a href="' + editLink + '" class="btn btn-primary" title="Modificar tarea">' +
                            '<i class="icon-edit"></i> <span>Modificar</span>' +
                            '</a>';
                    }
                    
                    // Botón Ejecutar (Success)
                    if (executeLink) {
                        newActions += '<a href="' + executeLink + '" class="btn btn-success" title="Ejecutar tarea ahora" onclick="return confirm(\'¿Desea ejecutar esta tarea ahora?\');">' +
                            '<i class="icon-play"></i> <span>Ejecutar</span>' +
                            '</a>';
                    }
                    
                    // Botón Eliminar (Danger) - SIEMPRE mostrar
                    if (deleteLink && deleteLink !== '#' && deleteLink.indexOf('delete') !== -1) {
                        newActions += '<a href="' + deleteLink + '" class="btn btn-danger" title="Eliminar tarea" onclick="return confirm(\'¿Está seguro de que desea eliminar esta tarea? Esta acción no se puede deshacer.\');">' +
                            '<i class="icon-trash"></i> <span>Eliminar</span>' +
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
                            '</a>';
                    }
                    
                    newActions += '</div>';
                    $actions.html(newActions);
                }
            }
        });
    }

    /**
<<<<<<< HEAD
     * Initialization when document is ready
     */
    $(document).ready(function() {
        // Wrap table immediately and after delays
=======
     * Inicialización cuando el documento está listo
     */
    $(document).ready(function() {
        // Envolver la tabla inmediatamente y después de delays
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
        wrapTable();
        setTimeout(wrapTable, 100);
        setTimeout(wrapTable, 500);
        
<<<<<<< HEAD
        // Process rows after a delay to ensure table is loaded
=======
        // Procesar las filas después de un delay para asegurar que la tabla esté cargada
>>>>>>> df7d71b524e3b90e8172f0780eda80a9fcceb1a0
        setTimeout(processTableRows, 300);
    });

})(jQuery || window.$);

