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

<script type="text/javascript">
$(document).ready(function() {
    setTimeout(function() {
        $('.table tbody tr').each(function() {
            var $row = $(this);
            var $actions = $row.find('td:last-child');
            var $statusCell = $row.find('td').eq($row.find('td').length - 2);
            
            if ($actions.length && $statusCell.length) {
                // Obtener el enlace de activar/desactivar de la columna "Activo"
                var statusLink = $statusCell.find('a').attr('href');
                var statusIcon = $statusCell.find('a i').attr('class') || 'icon-check';
                var isActive = $statusCell.find('span').hasClass('label-success') || $statusCell.find('a').hasClass('action-enabled');
                var statusText = isActive ? 'Desactivar' : 'Activar';
                
                // Guardar el HTML original antes de modificarlo
                var originalActionsHtml = $actions.html();
                
                // Buscar enlaces de editar y eliminar en la columna de acciones
                var editLink = null;
                var deleteLink = null;
                
                // Buscar enlace de editar - buscar por varios patrones
                $actions.find('a').each(function() {
                    var href = $(this).attr('href') || '';
                    var title = $(this).attr('title') || '';
                    var onclick = $(this).attr('onclick') || '';
                    if (href.indexOf('update') !== -1 || href.indexOf('edit') !== -1 || 
                        href.indexOf('addacccrontask') !== -1 || 
                        title.toLowerCase().indexOf('edit') !== -1 || 
                        title.toLowerCase().indexOf('editar') !== -1 ||
                        onclick.indexOf('edit') !== -1) {
                        editLink = href;
                        return false;
                    }
                });
                
                // Buscar enlace de eliminar
                $actions.find('a').each(function() {
                    var href = $(this).attr('href') || '';
                    var title = $(this).attr('title') || '';
                    var onclick = $(this).attr('onclick') || '';
                    if (href.indexOf('delete') !== -1 || 
                        title.toLowerCase().indexOf('delete') !== -1 || 
                        title.toLowerCase().indexOf('eliminar') !== -1 ||
                        onclick.indexOf('delete') !== -1) {
                        deleteLink = href;
                        return false;
                    }
                });
                
                // Si no encontramos los enlaces, buscar por patrones en el HTML
                if (!editLink) {
                    var editMatch = originalActionsHtml.match(/href=["']([^"']*update[^"']*)["']/i) || 
                                   originalActionsHtml.match(/href=["']([^"']*edit[^"']*)["']/i) ||
                                   originalActionsHtml.match(/href=["']([^"']*addacccrontask[^"']*)["']/i);
                    if (editMatch) {
                        editLink = editMatch[1];
                    }
                }
                
                if (!deleteLink) {
                    var deleteMatch = originalActionsHtml.match(/href=["']([^"']*delete[^"']*)["']/i);
                    if (deleteMatch) {
                        deleteLink = deleteMatch[1];
                    }
                }
                
                // Si aún no encontramos editLink, construirlo desde el ID
                if (!editLink) {
                    var idMatch = originalActionsHtml.match(/id_acccrontask=(\d+)/) || 
                                 $row.find('input[type="checkbox"]').val();
                    if (idMatch) {
                        var id = typeof idMatch === 'string' ? idMatch.match(/\d+/)[0] : idMatch;
                        var baseUrl = window.location.href.split('?')[0];
                        var token = '{$token|escape:'javascript':'UTF-8'}';
                        editLink = baseUrl + '?controller=AdminAccCronTask&id_acccrontask=' + id + '&updateacccrontask&token=' + token;
                    }
                }
                
                // Si aún no encontramos deleteLink, construirlo desde el ID
                if (!deleteLink || deleteLink === '#' || deleteLink.indexOf('delete') === -1) {
                    // Intentar obtener el ID de varias formas
                    var id = null;
                    
                    // Método 1: Buscar en el HTML original
                    var idMatch = originalActionsHtml.match(/id_acccrontask[=:](\d+)/i) || 
                                 originalActionsHtml.match(/deleteacccrontask[&?]id_acccrontask=(\d+)/i);
                    if (idMatch) {
                        id = idMatch[1] || idMatch[0].match(/\d+/)[0];
                    }
                    
                    // Método 2: Buscar en el checkbox
                    if (!id) {
                        var checkbox = $row.find('input[type="checkbox"][name*="acccrontask"]');
                        if (checkbox.length) {
                            id = checkbox.val();
                        }
                    }
                    
                    // Método 3: Buscar en cualquier input o data attribute
                    if (!id) {
                        var rowId = $row.attr('id') || $row.data('id');
                        if (rowId) {
                            var numMatch = rowId.match(/\d+/);
                            if (numMatch) {
                                id = numMatch[0];
                            }
                        }
                    }
                    
                    // Si encontramos el ID, construir el enlace
                    if (id) {
                        // Obtener la URL base y el token
                        var baseUrl = '{$current_index|escape:'javascript':'UTF-8'}';
                        var token = '{$token|escape:'javascript':'UTF-8'}';
                        
                        // Si no tenemos baseUrl del template, construirla desde window.location
                        if (!baseUrl || baseUrl === '') {
                            baseUrl = window.location.href.split('?')[0] + '?controller=AdminAccCronTask';
                        }
                        
                        // Construir el enlace de eliminar
                        deleteLink = baseUrl + '&id_acccrontask=' + id + '&deleteacccrontask&token=' + token;
                    }
                }
                
                // Construir los nuevos botones con Modificar como botón principal
                if (editLink || deleteLink) {
                    var newActions = '<div class="btn-group">';
                    
                    // Botón principal: Modificar
                    if (editLink) {
                        newActions += '<a href="' + editLink + '" class="btn btn-default" title="Modificar">' +
                            '<i class="icon-edit"></i> Modificar' +
                            '</a>';
                    }
                    
                    // Desplegable solo con Eliminar
                    if (deleteLink && deleteLink !== '#' && deleteLink.indexOf('delete') !== -1) {
                        newActions += '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                            '<span class="caret"></span>' +
                            '</button>' +
                            '<ul class="dropdown-menu">' +
                            '<li><a href="' + deleteLink + '" onclick="return confirm(\'¿Está seguro de que desea eliminar esta tarea?\');"><i class="icon-trash"></i> Eliminar</a></li>' +
                            '</ul>';
                    } else if (deleteLink && deleteLink !== '#') {
                        // Si tenemos un enlace pero no es válido, intentar construir uno mejor
                        var idMatch = $row.find('input[type="checkbox"]').val() || 
                                     originalActionsHtml.match(/\d+/);
                        if (idMatch) {
                            var id = typeof idMatch === 'string' ? idMatch : idMatch[0];
                            var baseUrl = '{$current_index|escape:'javascript':'UTF-8'}';
                            var token = '{$token|escape:'javascript':'UTF-8'}';
                            
                            // Si no tenemos baseUrl del template, construirla desde window.location
                            if (!baseUrl || baseUrl === '') {
                                baseUrl = window.location.href.split('?')[0] + '?controller=AdminAccCronTask';
                            }
                            
                            var validDeleteLink = baseUrl + '&id_acccrontask=' + id + '&deleteacccrontask&token=' + token;
                            newActions += '<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
                                '<span class="caret"></span>' +
                                '</button>' +
                                '<ul class="dropdown-menu">' +
                                '<li><a href="' + validDeleteLink + '" onclick="return confirm(\'¿Está seguro de que desea eliminar esta tarea?\');"><i class="icon-trash"></i> Eliminar</a></li>' +
                                '</ul>';
                        }
                    }
                    
                    newActions += '</div>';
                    
                    $actions.html(newActions);
                }
            }
        });
    }, 500);
});
</script>

