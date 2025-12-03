{*
* 2007-2024 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2024 PrestaShop SA
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

{$list nofilter}

<style>
/* Estilos Bootstrap 5 para los botones de acción - pequeños y en línea */
.acccrontask-actions {
    display: inline-flex;
    gap: 0.25rem;
    align-items: center;
    flex-wrap: nowrap;
}

.acccrontask-actions .btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    padding: 0.25rem 0.5rem;
    text-transform:initial !important;
    font-size: 0.75rem;
    font-weight: 500;
    line-height: 1.2;
    height: 28px;
    text-align: center;
    text-decoration: none;
    white-space: nowrap;
    vertical-align: middle;
    cursor: pointer;
    user-select: none;
    border: 1px solid transparent;
    border-radius: 0.25rem;
    transition: color 0.15s ease-in-out, background-color 0.15s ease-in-out, border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
}

.acccrontask-actions .btn:hover {
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075), 0 0.25rem 0.5rem rgba(0, 0, 0, 0.15);
}

.acccrontask-actions .btn:active {
    transform: translateY(0);
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
}

.acccrontask-actions .btn-primary {
    color: #fff;
    background-color: #0d6efd;
    border-color: #0d6efd;
}

.acccrontask-actions .btn-primary:hover {
    color: #fff;
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.acccrontask-actions .btn-success {
    color: #fff;
    background-color: #198754;
    border-color: #198754;
}

.acccrontask-actions .btn-success:hover {
    color: #fff;
    background-color: #157347;
    border-color: #146c43;
}

.acccrontask-actions .btn-danger {
    color: #fff;
    background-color: #dc3545;
    border-color: #dc3545;
}

.acccrontask-actions .btn-danger:hover {
    color: #fff;
    background-color: #bb2d3b;
    border-color: #b02a37;
}

.acccrontask-actions .btn i {
    font-size: 0.75rem;
    line-height: 1;
    margin: 0;
}

.acccrontask-actions .btn span {
    font-size: 0.75rem;
}
</style>

<script type="text/javascript">
$(document).ready(function() {
    setTimeout(function() {
        $('.table tbody tr').each(function() {
            var $row = $(this);
            var $actions = $row.find('td:last-child');
            
            if ($actions.length) {
                var originalActionsHtml = $actions.html();
                var editLink = null;
                var deleteLink = null;
                var executeLink = null;
                
                // Buscar todos los enlaces en la columna de acciones
                $actions.find('a').each(function() {
                    var $link = $(this);
                    var href = $link.attr('href') || '';
                    var title = ($link.attr('title') || '').toLowerCase();
                    var text = $link.text().toLowerCase();
                    var onclick = ($link.attr('onclick') || '').toLowerCase();
                    var className = ($link.attr('class') || '').toLowerCase();
                    
                    // Identificar tipo de enlace
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
                
                // Buscar también en el HTML original por patrones (más exhaustivo)
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
                
                // Si no encontramos los enlaces, intentar construirlos desde el ID
                var idMatch = originalActionsHtml.match(/id_acccrontask[=:](\d+)/i) || 
                             $row.find('input[type="checkbox"][name*="acccrontask"]').val() ||
                             originalActionsHtml.match(/\bid_acccrontask=(\d+)/i) ||
                             $row.find('td').first().text().match(/\d+/);
                
                var id = null;
                if (idMatch) {
                    id = idMatch[1] || (idMatch[0] ? idMatch[0].match(/\d+/)[0] : null);
                }
                
                // Si aún no tenemos ID, buscar en toda la fila
                if (!id) {
                    var rowText = $row.text();
                    var idFromRow = rowText.match(/id_acccrontask[=:]?(\d+)/i);
                    if (idFromRow) {
                        id = idFromRow[1];
                    }
                }
                
                if (id) {
                    var baseUrl = '{$current_index|escape:'javascript':'UTF-8'}';
                    var token = '{$token|escape:'javascript':'UTF-8'}';
                    
                    if (!baseUrl || baseUrl === '') {
                        baseUrl = window.location.href.split('?')[0] + '?controller=AdminAccCronTask';
                    }
                    
                    if (!editLink) {
                        editLink = baseUrl + '&id_acccrontask=' + id + '&updateacccrontask&token=' + token;
                    }
                    // SIEMPRE construir deleteLink si tenemos ID
                    if (!deleteLink || deleteLink === '#') {
                        deleteLink = baseUrl + '&id_acccrontask=' + id + '&deleteacccrontask&token=' + token;
                    }
                    if (!executeLink) {
                        executeLink = baseUrl + '&id_acccrontask=' + id + '&executeNowacccrontask&token=' + token;
                    }
                }
                
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
                            '</a>';
                    }
                    
                    newActions += '</div>';
                    $actions.html(newActions);
                }
            }
        });
    }, 300);
});
</script>

