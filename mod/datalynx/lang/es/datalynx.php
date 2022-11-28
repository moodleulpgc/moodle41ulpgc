<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local language pack from https://localhost/moodle39ulpgc
 *
 * @package    mod
 * @subpackage datalynx
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['action'] = 'Acción';
$string['actions'] = 'Acciones';
$string['activate'] = 'activar';
$string['addline'] = 'Añadir {$a}';
$string['addoptions'] = 'Añadir opciones';
$string['admin'] = 'Administrador';
$string['admissibleroles'] = 'Roles admitidos';
$string['admissibleroles_error'] = 'Por favor, seleccione al menos un rol.';
$string['admissibleroles_help'] = 'Los usuarios que tengan alguno de estos roles podrán incorporarse al grupo. 
Se debe seleccionar al menos un rol.';
$string['after'] = 'después';
$string['alignment'] = 'Alineación';
$string['allof'] = 'todos';
$string['allowaddoption'] = 'Permitir añadir opciones';
$string['allowsubscription'] = 'Permitir subscripción manual';
$string['allowsubscription_help'] = 'Marque e4sta opción si desea permitir que 
los usuarios se puedan agregar por si mismos a Equipos creados por otros. 
Esto se realiza a través de una etiqueta de extensión (por ejemplo, 
[[&lt;fieldname&gt;:subscribe]]), que modifica el campo para mostrar un enlace en el modo de visualización. 
Pinchando en ese enlace un usuario puede auto-agregarse al Equipo, 
si le está permitido por la configuración del Equipo. ';
$string['allowunsubscription'] = 'Permitir desubscripción manual';
$string['allowunsubscription_help'] = 'Marcar para permitir a los usuarios  
abandonar por si mismos un Equipo al que hayan unido.
Si se deshabilita, los miembros de un Equipo solo pueden ser eliminados por el usuario que creó el Equipo.';
$string['alltime'] = 'Siempre';
$string['alphabeticalorder'] = '¿Ordenar las opciones alfabéticamente al editar una entrada?';
$string['and'] = 'Y';
$string['andor'] = 'y/o ...';
$string['anyof'] = 'alguno de';
$string['approval_none'] = 'No requerido';
$string['approval_required_new'] = 'Requerido solo para nuevas';
$string['approval_required_update'] = 'Requerido para nuevas y editadas';
$string['approved'] = 'aprobado';
$string['approvednot'] = 'no aprobado';
$string['ascending'] = 'Ascendente';
$string['asdisplay'] = 'Usar Plantilla';
$string['author'] = 'Autor';
$string['authorinfo'] = 'Info. Autor';
$string['autocompletion'] = 'Autocompletado';
$string['autocompletion_help'] = 'Indique si el Autocompletado debe estar activo en el modo de edición.';
$string['autocompletion_textfield'] = 'Campo de Texto';
$string['autocompletion_textfield_help'] = 'Seleccione el módulo y campo de texto para devolver datos de autocompletado.';
$string['avoidaddanddeletesimultaneously'] = 'No se deben añadir y borrar opciones simultáneamente en una única operación.
Primero debe borrar las opciones y guardar, solo después renombrar o agregar opciones y guardar otra vez.';
$string['before'] = 'delante';
$string['behavior'] = 'Comportamiento';
$string['behavioradd'] = 'Añadir comportamiento';
$string['behaviors'] = 'Comportamientos';
$string['between'] = 'entre';
$string['blankfilter'] = 'Filtro vacío';
$string['browse'] = 'Ver';
$string['check_enable'] = 'Se debe activar la casilla \'habilitar\' para confirmar la validez del valor seleccionado.';
$string['columns'] = 'columnas';
$string['comment'] = 'Comentario';
$string['commentadd'] = 'Añadir comentario';
$string['commentbynameondate'] = 'por {$a->name} - {$a->date}';
$string['commentdelete'] = '¿Desea borrar este comentario?';
$string['commentdeleted'] = 'Comentario borrado';
$string['commentedit'] = 'Editar comentario';
$string['commentempty'] = 'Comentario vacío';
$string['commentinputtype'] = 'Tipo de comentario';
$string['comments'] = 'Comentarios';
$string['commentsallow'] = '¿Permitir comentarios?';
$string['commentsaved'] = 'Comentario guardado';
$string['commentsn'] = '{$a} comentarios';
$string['commentsnone'] = 'Sin comentarios';
$string['completionentries'] = 'Número de entradas (aprobadas)';
$string['completionentriesgroup'] = 'Requiere entradas (aprobadas)';
$string['completionentriesgroup_help'] = '¡Verifique el estado de aprobación de las entradas de arriba!!<br />
Número de entradas (aprobadas): Entradas que debe completar el usuario. 
Si está activo \'Requiere aprobación\': Número de entradas es igual al número de entradas aprobadas entradas.';
$string['configanonymousentries'] = 'Esta opción habilitará la disponibilidad de entradas invitadas/anónimas en todos los Registros de datos. 
Es preciso activar manualmente las entradas anónimas en cada Registro de datos.';
$string['configenablerssfeeds'] = 'Esta opción habilitará la disponibilidad de canales RSS en todos los Registros de datos. 
Es preciso activar manualmente los canales RSS en cada Registro de datos.';
$string['configmaxentries'] = 'Este parámetro determina el nº máximo de entradas que pueden añadirse al Registro de datos por un usuario.';
$string['configmaxfields'] = 'Este parámetro determina el nº máximo de campos que pueden añadirse al Registro de datos por un usuario.';
$string['configmaxfilters'] = 'Este parámetro determina el nº máximo de filtros que pueden añadirse al Registro de datos por un usuario.';
$string['configmaxviews'] = 'Este parámetro determina el nº máximo de vistas que pueden añadirse al Registro de datos por un usuario.';
$string['confirmbehaviordelete'] = 'Ha solicitado borrar this field behavior!';
$string['confirmbehaviorduplicate'] = 'Ha solicitado duplicar this field behavior!';
$string['confirmfieldgroupdelete'] = '¡Ha solicitado borrar este conjunto!';
$string['confirmfieldgroupduplicate'] = '¡Ha solicitado duplicar este conjunto!';
$string['confirmrendererdelete'] = 'Ha solicitado borrar this field renderer!';
$string['confirmrendererduplicate'] = 'Ha solicitado duplicar this field renderer!';
$string['contains'] = 'contiene';
$string['convert'] = 'Convertir';
$string['converttoeditor'] = 'Convertir a campo Editor';
$string['copyof'] = 'Copia de {$a}';
$string['correct'] = 'Correcto';
$string['csscode'] = 'Código CSS';
$string['cssinclude'] = 'CSS';
$string['cssincludes'] = 'Incluir CSS externo';
$string['csssaved'] = 'CSS guardado';
$string['cssupload'] = 'Cargar archivos CSS';
$string['csvdelimiter'] = 'delimitador';
$string['csvenclosure'] = 'marcador de texto';
$string['csvfailed'] = 'No se puede leer el texto bruto del archivo CSV.';
$string['csvoutput'] = 'Salida CSV';
$string['csvsettings'] = 'Opciones CSV';
$string['csvwithselecteddelimiter'] = 'Texto <acronym title=\\"Comma Separated Values\\">CSV</acronym> con el delimitador seleccionado:';
$string['custom'] = 'Plantilla personalizada';
$string['customfilter'] = 'Filtro a medida';
$string['customfilteradd'] = 'Añadir un filtro a medida';
$string['customfilternew'] = 'Nuevo filtro a medida';
$string['customfilters'] = 'Filtros a medida';
$string['customfiltersnoneindatalynx'] = 'No hay filtros a medida definidos en este Registro de datos.';
$string['datalynx_commentadded'] = 'Comentario añadido';
$string['datalynx_csssaved'] = 'CSS personalizado guardado';
$string['datalynx_entryadded'] = 'Entrada añadida';
$string['datalynx_entryapproved'] = 'Entradas aprobadas';
$string['datalynx_entrydeleted'] = 'Entradas borradas';
$string['datalynx_entrydisapproved'] = 'Entradas rechazadas';
$string['datalynx_entryupdated'] = 'Entradas actualizadas';
$string['datalynx_jssaved'] = 'JavaScript personalizado guardado';
$string['datalynx_memberadded'] = 'Miembro de Equipo añadido';
$string['datalynx_memberremoved'] = 'Miembro de Equipo eliminado';
$string['datalynx_ratingadded'] = 'Valoración añadida';
$string['datalynx_ratingdeleted'] = 'Valoración borrada';
$string['datalynx_ratingupdated'] = 'Valoración actualizada';
$string['datalynx_team_updated'] = 'Equipo actualizado';
$string['datalynx:addinstance'] = 'Añadir un nuevo Registro de datos';
$string['datalynx:approve'] = 'Aprobar entradas pendientes';
$string['datalynx:comment'] = 'Escribir comentarios';
$string['datalynx:editprivilegeadmin'] = 'Privilegio de acceso editor admin';
$string['datalynx:editprivilegeguest'] = 'Privilegio de acceso editor invitado';
$string['datalynx:editprivilegemanager'] = 'Privilegio de acceso editor Mánager';
$string['datalynx:editprivilegestudent'] = 'Privilegio de acceso editor estudiante';
$string['datalynx:editprivilegeteacher'] = 'Privilegio de acceso editor profesor';
$string['datalynx:editrestrictedfields'] = 'Editar campos restringidos';
$string['datalynx:exportallentries'] = 'Exportar todas las entradas';
$string['datalynx:exportentry'] = 'Exportar entrada';
$string['datalynx:exportownentry'] = 'Exportar entradas propias';
$string['datalynx:managecomments'] = 'Gestionar comentarios';
$string['datalynx:manageentries'] = 'Gestionar entradas';
$string['datalynx:managepresets'] = 'Gestionar paquetes';
$string['datalynx:manageratings'] = 'Gestionar valoraciones';
$string['datalynx:managetemplates'] = 'Gestionar Plantillas';
$string['datalynx:notifycommentadded'] = 'Notificar al añadir comentario';
$string['datalynx:notifyentryadded'] = 'Notificar al añadidas entrada';
$string['datalynx:notifyentryapproved'] = 'Notificar al aprobar entrada';
$string['datalynx:notifyentrydeleted'] = 'Notificar al borrar entrada';
$string['datalynx:notifyentrydisapproved'] = 'Notificar al rechazar entrada';
$string['datalynx:notifyentryupdated'] = 'Notificar al actualizar entrada';
$string['datalynx:notifymemberadded'] = 'Inform users about being añadidas as a team member';
$string['datalynx:notifymemberremoved'] = 'Inform users about being eliminado as a team member';
$string['datalynx:notifyratingadded'] = 'Notificar al añadir valoración';
$string['datalynx:notifyratingupdated'] = 'Notificar al actualizar valoración';
$string['datalynx:notifyteamupdated'] = 'Notificar al team update';
$string['datalynx:presetsviewall'] = 'Ver todos los paquetes';
$string['datalynx:rate'] = 'Valorar entradas';
$string['datalynx:ratingsview'] = 'Ver valoraciones';
$string['datalynx:ratingsviewall'] = 'Ver todas las valoraciones';
$string['datalynx:ratingsviewany'] = 'Ver cualquier valoración';
$string['datalynx:teamsubscribe'] = 'Suscribirse o unirse a Equipos';
$string['datalynx:viewanonymousentry'] = 'Ver entradas anónimas';
$string['datalynx:viewdrafts'] = 'Ver borradores';
$string['datalynx:viewentry'] = 'Ver entradas';
$string['datalynx:viewindex'] = 'Ver índice';
$string['datalynx:viewprivilegeadmin'] = 'Privilegio de acceso de vista de administrador';
$string['datalynx:viewprivilegeguest'] = 'Privilegio de acceso de vista de invitado';
$string['datalynx:viewprivilegemanager'] = 'Privilegio de acceso de vista de mánager';
$string['datalynx:viewprivilegestudent'] = 'Privilegio de acceso de vista de estudiante';
$string['datalynx:viewprivilegeteacher'] = 'Privilegio de acceso de vista de profesor';
$string['datalynx:viewstatistics'] = 'Ver estadísticas';
$string['datalynx:writeentry'] = 'Escribir entradas';
$string['defaultbehavior'] = 'Comportamiento predefinido';
$string['defaultfilterlabel'] = 'Filtro predefinido ({$a})';
$string['defaultrenderer'] = 'Visualizador predefinido';
$string['defaultview'] = 'D';
$string['deletefieldfilterwarning'] = 'Warning! You are attempting to borrar following fields:{$a->fieldlist}However, filtros listed below are still using some de these fields:{$a->filterlist}You will have to borrar these filtros manually first before you may proceed.';
$string['deletenotenrolled'] = 'Borrar entradas por usuarios no matriculados';
$string['deleteoption'] = '¿Borrar?';
$string['deletetag'] = 'Borrar etiqueta';
$string['deletingbehavior'] = 'Borrando comportamiento de campo "{$a}"';
$string['deletingfieldgroup'] = 'Borrando conjunto de campos "{$a}"';
$string['deletingrenderer'] = 'Borrando visualizador de campo "{$a}"';
$string['descending'] = 'Descendente';
$string['dfintervalcount'] = 'Número de intervalos';
$string['dfintervalcount_help'] = 'Seleccione cuántos intervalos deben ser desbloqueados';
$string['dflateallow'] = 'Mensajes tardíos';
$string['dflateuse'] = 'Permitir mensajes tardíos';
$string['dfratingactivity'] = 'Evaluación de la actividad';
$string['dftimeavailable'] = 'Disponible desde';
$string['dftimedue'] = 'Plazo';
$string['dftimeinterval'] = 'Esperar hasta que las siguente entrada sea desbloqueada';
$string['dftimeinterval_help'] = 'Seleccionar un periodo hasta que los próxima entrada sea desbloqueada para el usuario';
$string['dfupdatefailed'] = '¡Fallo al actualizar Registro de datos!';
$string['disabled'] = 'Mostrar elementos deshabilitados';
$string['disapproved'] = 'No aprobadas';
$string['displaytemplate'] = 'Mostrar plantilla';
$string['displaytemplate_help'] = 'Especifique el texto HTML que reemplaza a la etiqueta de campo 
en el modo de Vista. Para especificar la posición del valor real del campo use la etiqueta #value en este texto HTML. ';
$string['documenttype'] = 'Tipo de documento';
$string['dots'] = '...';
$string['download'] = 'Descargar';
$string['duplicatename'] = 'El nombre ya existe. Por favor elija otro.';
$string['duplicatingbehavior'] = 'Duplicando comportamiento de campo "{$a}"';
$string['duplicatingfieldgroup'] = 'Duplicando conjunto de campos "{$a}"';
$string['duplicatingrenderer'] = 'Duplicando visualizador de campo "{$a}"';
$string['editable'] = 'Editable';
$string['editableby'] = 'Editable por';
$string['editing'] = 'Editando';
$string['editingbehavior'] = 'Editando field behavior "{$a}"';
$string['editingfieldgroup'] = 'Editando conjunto de campos "{$a}"';
$string['editingrenderer'] = 'Editando visualizador de campo "{$a}"';
$string['editmode'] = 'Modo de Edición';
$string['editordisable'] = 'Desactivar editor';
$string['editorenable'] = 'Activar editor';
$string['edittemplate'] = 'Editar Plantilla';
$string['edittemplate_help'] = 'Specify HTML Plantilla to replace the field etiqueta in edit mode. To specify the position de the actual input element, use #input etiqueta within the Plantilla.';
$string['email'] = 'Email';
$string['embed'] = 'Incrustar';
$string['empty'] = 'vacío';
$string['enabled'] = 'Habilitado';
$string['entries'] = 'Entradas';
$string['entriesadded'] = '{$a} entrada(s) añadidas';
$string['entriesanonymous'] = 'Permitir anónimas entradas';
$string['entriesappended'] = '{$a} entrada(s) añadidas';
$string['entriesapproved'] = '{$a} entrada(s) aprobadas';
$string['entriesconfirmadd'] = 'Ha solicitado duplicar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmapprove'] = 'Ha solicitado aprobar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmdelete'] = 'Ha solicitado borrar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmduplicate'] = 'Ha solicitado duplicar {$a} entrada(s). ¿Desea continuar?';
$string['entriesconfirmupdate'] = 'Ha solicitado actualizar {$a} entrada(s). ¿Desea continuar?';
$string['entriescount'] = '{$a} entrada(s)';
$string['entriesdeleteall'] = 'Borrar todas las entradas';
$string['entriesdeleted'] = '{$a} entrada(s) borradas';
$string['entriesdisapproved'] = '{$a} entrada(s) rechazadas';
$string['entriesduplicated'] = '{$a} entrada(s) duplicadas';
$string['entriesfound'] = 'Encontradas {$a} entrada(s)';
$string['entriesimport'] = 'Importar entradas';
$string['entrieslefttoadd'] = 'Debe añadir {$a} entrada/entradas más para completar la actividad';
$string['entrieslefttoaddtoview'] = 'Debe añadir {$a} entrada/entradas más antes de que pueda acceder a las entradas de otros participantes.';
$string['entriesmax'] = 'Máximo de entradas';
$string['entriesmax_help'] = 'Número de máximo de entradas permitidas, -1 permite entradas sin límite';
$string['entriesnotsaved'] = 'Ninguna entrada guardada. Por favor, verifique el formato del archivo cargado.';
$string['entriespending'] = 'Pendiente';
$string['entriesrequired'] = 'Entradas requeridas';
$string['entriessaved'] = '{$a} entrada(s) guardadas';
$string['entriestoview'] = 'Entradas requeridas antes de dar acceso';
$string['entriesupdated'] = '{$a} entrada(s) actualizadas';
$string['entry'] = 'Entrada';
$string['entryaddmultinew'] = 'Añadir nuevas entradas';
$string['entryaddnew'] = 'Añadir una nueva entrada';
$string['entryauthor'] = 'Autor de la entrada';
$string['entryinfo'] = 'Información';
$string['entrylockonapproval'] = 'Bloquear al aprobar';
$string['entrylockonratings'] = 'Bloquear al valorar';
$string['entrylocks'] = 'Bloqueos en Entrada';
$string['entrynew'] = 'Nueva entrada';
$string['entrynoneforaction'] = 'No hay entradas para la acción requerida';
$string['entrynoneindatalynx'] = 'No hay entradas en Registro de datos';
$string['entryrating'] = 'Valoración de Entrada';
$string['entrysaved'] = 'Se ha guardado';
$string['entrysettings'] = 'Opciones de Entrada';
$string['entrysettingsupdated'] = 'Opciones de Entrada actualizadas';
$string['entrytemplate'] = 'Plantilla de Entrada';
$string['entrytemplate_help'] = 'Plantilla de Entrada';
$string['entrytimelimit'] = 'Editando plazo límite (minutos)';
$string['entrytimelimit_help'] = 'Minutos disponibles hasta que la edición sea deshabilitada, -1 para edición sin límite';
$string['equal'] = 'igual';
$string['err_numeric'] = 'Debe introducir un número aquí. Ejemplo: 0.00 o 0.3 o 387';
$string['event_comment_created'] = 'Comentario creado';
$string['event_entry_approved'] = 'Entrada aprobada';
$string['event_entry_created'] = 'Entrada creada';
$string['event_entry_deleted'] = 'Entrada borrada';
$string['event_entry_disapproved'] = 'Entrada rechazada';
$string['event_entry_updated'] = 'Entrada actualizada';
$string['event_rating_added'] = 'Valoración añadida';
$string['event_rating_deleted'] = 'Valoración borrada';
$string['event_rating_updated'] = 'Valoración actualizada';
$string['event_team_updated'] = 'Equipo actualizado';
$string['eventsettings'] = 'Opciones del evento';
$string['exactly'] = 'exactamente';
$string['existingoptions'] = 'Editar opciones existentes';
$string['export'] = 'Exportar';
$string['exportadd'] = 'Añadir una nueva vista de exportación';
$string['exportall'] = 'Exportar todo';
$string['exportcontent'] = 'Exportar contenido';
$string['exportnoneindatalynx'] = 'No hay exportaciones definidas en este Registro de datos.';
$string['exportpage'] = 'Exportar página';
$string['field'] = 'Campo';
$string['field_has_duplicate_entries'] = 'Hay entradas duplicadas, 
por lo tanto no es posible definir este campo como "Único" en este momento.';
$string['fieldadd'] = 'Añadir un campo';
$string['fieldallowautolink'] = 'Permitir autoenlace';
$string['fieldattributes'] = 'Atributos del Campo';
$string['fieldcreate'] = 'Crear nuevo campo';
$string['fielddescription'] = 'Descripción del Campo';
$string['fieldedit'] = 'Editando \'{$a}\'';
$string['fieldeditable'] = 'Editable';
$string['fieldedits'] = 'Número de ediciones';
$string['fieldgroupfields'] = 'Campos del conjunto';
$string['fieldgroupfields_help'] = 'Campos que se repiten juntos como un conjunto. 
El orden de los campos es siempre alfabético, así que dele los nombre apropiados para mantener el orden deseado';
$string['fieldgroups'] = 'Conjuntos de campos';
$string['fieldgroupsadd'] = 'Añadir conjunto';
$string['fieldids'] = 'Campo ids';
$string['fieldlabel'] = 'Etiqueta del Campo';
$string['fieldlabel_help'] = 'La etiqueta de campo permite definir un texto que se añade a la Vista con un códido como [[fieldname@]].
TEl código sigue la visibilidad del campo, no se muestra si el campo está oculto.
This field pattern observes the field visibility and is hidden if the field is set to be hidden. 
La etiqueta de campo también funciona como una plantilla de visualización. 
Por ejemplo, con un campo numérico denominado "Número" y una etiqueta de campo definida como "Ha obtenido [[Número]] créditos", 
en una entrada en la que el campo tiene el valor 47, el código [[Número@]] se mostrará como "Ha obtenido 47 créditos".';
$string['fieldlist'] = 'Campos de búsqueda';
$string['fieldmappings'] = 'Mapeo del Campo';
$string['fieldname'] = 'Nombre del campo';
$string['fieldnew'] = 'Nuevo campo {$a}';
$string['fieldnoneforaction'] = 'No se encontraron campos para la acción requerida.';
$string['fieldnoneindatalynx'] = 'No hay campos definidos en este Registro de datos.';
$string['fieldnonematching'] = 'No hay campos coincidentes';
$string['fieldnotmatched'] = 'Los siguientes campos en su archivo no son conocidos en este Registro de datos: {$a}';
$string['fieldoptions'] = 'Opciones (una por línea)';
$string['fieldoptionsdefault'] = 'Valores por defecto (una por línea)';
$string['fieldoptionsseparator'] = 'Separador de opciones';
$string['fieldrequired'] = 'Campo obligatorio; debe introducir un valor.';
$string['fieldrules'] = 'Reglas de edición del Campo';
$string['fields'] = 'Campos';
$string['fieldsadded'] = 'Campos añadidos';
$string['fieldsconfirmdelete'] = 'Ha solicitado borrar {$a} campo(s). ¿Desea continuar?';
$string['fieldsconfirmduplicate'] = 'Ha solicitado duplicar {$a} campo(s). ¿Desea continuar?';
$string['fieldsdeleted'] = 'Campos borradas. Puede ser necesario actualizar las opciones de ordenamiento predefinidas.';
$string['fieldsimportsettings'] = 'Importarsettings';
$string['fieldsmax'] = 'Máximo de campos';
$string['fieldsnonedefined'] = 'Sin campos definidos';
$string['fieldsupdated'] = 'Campos actualizados';
$string['fieldtype'] = 'Tipo de campo';
$string['fieldvisibility'] = 'Visible para';
$string['fieldvisibleall'] = 'Todos';
$string['fieldvisiblenone'] = 'Solo administradores';
$string['fieldvisibleowner'] = 'Propietarios y administradores';
$string['fieldwidth'] = 'Ancho';
$string['fileexist'] = 'existe';
$string['filemaxsize'] = 'Tamaño total de los archivos subidos';
$string['filemissing'] = 'no existe';
$string['filesmax'] = 'Nº máximo de archivos subidos';
$string['filetypeany'] = 'Cualquier tipo de archivo';
$string['filetypeaudio'] = 'Archivos de Audio';
$string['filetypegif'] = 'Archivos gif';
$string['filetypehtml'] = 'Archivos Html';
$string['filetypeimage'] = 'Archivos de Imagen';
$string['filetypejpg'] = 'Archivos jpg';
$string['filetypepng'] = 'Archivos png';
$string['filetypes'] = 'Tipos de archivo aceptados';
$string['filter'] = 'Filtro';
$string['filteradd'] = 'Añadir un filtro';
$string['filteradvanced'] = 'Filtro a medida';
$string['filterbypage'] = 'Por página';
$string['filtercancel'] = 'Cancelar filtro';
$string['filtercreate'] = 'Crear un nuevo filtro';
$string['filtercurrent'] = 'Fitro actual';
$string['filtercustomsearch'] = 'Opciones de búsqueda';
$string['filtercustomsort'] = 'Ordenado';
$string['filterdescription'] = 'Descripción del filtro';
$string['filteredit'] = 'Editando \'{$a}\'';
$string['filterformadd'] = 'Añadido Formulario de Filtro';
$string['filterforms'] = 'Formulario de Filtro';
$string['filtergroupby'] = 'Agrupar por';
$string['filterincomplete'] = 'El patrón de búsqueda debe ser completado.';
$string['filtermy'] = 'Mi filtro';
$string['filtername'] = 'Autoenlazado de Registro de datos';
$string['filternew'] = 'Nuevo filtro';
$string['filternoneforaction'] = 'No hay filtros para la acción requerida';
$string['filterperpage'] = 'Nº por página';
$string['filters'] = 'Filtros';
$string['filtersadded'] = '{$a} filtro(s) añadidos';
$string['filtersave'] = 'Guardar filtro';
$string['filtersconfirmdelete'] = 'Ha solicitado borrar {$a} filtro(s). ¿Desea continuar?';
$string['filtersconfirmduplicate'] = 'Ha solicitado duplicar {$a} filtro(s). ¿Desea continuar?';
$string['filtersdeleted'] = '{$a} filtro(s) borrados';
$string['filtersduplicated'] = '{$a} filtro(s) duplicados';
$string['filtersearchfieldlabel'] = 'Buscar por campo ';
$string['filterselection'] = 'Selección';
$string['filtersimplesearch'] = 'Búsqueda simple';
$string['filtersmax'] = 'Máximo de filtros';
$string['filtersnonedefined'] = 'No hay filtros definidos';
$string['filtersnoneindatalynx'] = 'No hay filtros definidos en este Registro de datos.';
$string['filtersortfieldlabel'] = 'Ordenar por campo ';
$string['filtersupdated'] = '{$a} filtro(s) actualizados';
$string['filterupdate'] = 'Actualizar un  filtro existente';
$string['filterurlquery'] = 'Búsqueda Url';
$string['filteruserreset'] = '** Reiniciar el  filtro';
$string['first'] = 'Primero';
$string['firstdayofweek'] = 'Lunes';
$string['formemptyadd'] = '¡No se ha rellenado ningún campo!';
$string['fromaftertoday_error'] = '\'From\' fecha cannot be set after today\'s fecha!';
$string['fromdate'] = 'Desde fecha';
$string['fromfile'] = 'Importar de archivo ZIP';
$string['fromto_error'] = 'La fecha \'Desde\' no puede ser posterior a la fecha \'Hasta\'';
$string['fulltextsearch'] = 'Búsqueda de texto completo';
$string['generalactions'] = 'Acciones generales';
$string['getstarted'] = 'Esta Registro de datos parece ser nuevo o con configuración incompleta. 
Para inicializar el Registro de datos 
<ul><li>aplique un paquete en la pestaña {$a->presets}</li></ul> o 
<ul><li>añada campos en la pestaña {$a->fields}</li><li>añada Vistas pestaña {$a->views}</li></ul>';
$string['grade'] = 'Calificación';
$string['gradeinputtype'] = 'Tipo de calificación';
$string['gradeitem'] = 'Ítem de Calificación';
$string['grading'] = 'Evaluación';
$string['gradingmethod'] = 'Método de calificación';
$string['gradingsettings'] = 'Opciones de evaluación de la actividad';
$string['greater_equal'] = 'mayor o igual';
$string['greater_than'] = 'mayor que';
$string['groupentries'] = 'Entradas de Grupo';
$string['groupinfo'] = 'Información de Grupo';
$string['guest'] = 'Invitado';
$string['headercss'] = 'Estilos CSS personalizados para todas las vistas';
$string['headerjs'] = 'JavaScript personalizado para todas las vistas';
$string['hidden'] = 'Oculto';
$string['hideline'] = 'Ocultar la última línea';
$string['horizontal'] = 'Horizontal';
$string['iamteammember'] = 'Soy miembro del Equipo';
$string['id'] = 'ID';
$string['import'] = 'Importar';
$string['importadd'] = 'Añadir una nueva Vista de Importación';
$string['importnoneindatalynx'] = 'No hay importaciones definidas en este Registro de datos.';
$string['in'] = 'en';
$string['incorrect'] = 'Incorrecto';
$string['index'] = 'Índice';
$string['insufficiententries'] = 'Se necesitan más entradas para acceder a este Registro de datos';
$string['internal'] = 'Interno';
$string['intro'] = 'Introducción';
$string['invalidname'] = 'Por favor, escoja otro nombre para este {$a}';
$string['invalidrate'] = 'Valoración de Registro de datos  ({$a}) inválida';
$string['invalidurl'] = 'La URL introducida no es válida';
$string['is'] = 'ES';
$string['isdefault'] = 'Vista predefinida';
$string['isedit'] = 'Editar vista';
$string['ismore'] = 'Vista detallada';
$string['jscode'] = 'Código Javascript';
$string['jsinclude'] = 'JS';
$string['jsincludes'] = 'Incluir javascript externo';
$string['jssaved'] = 'Javascript guardado';
$string['jsupload'] = 'Cargar archivos javascript';
$string['label'] = 'Etiqueta';
$string['less_equal'] = 'menor o igual';
$string['less_than'] = 'menor de';
$string['limitchoice'] = 'Limitar opciones a los usuarios';
$string['limitchoice_error'] = 'Ya ha seleccionado la opción \'{$a}\' el número máximo de veces permitidas';
$string['limitchoice_help'] = 'Activar esto para evitar que el usuario seleccione 
la misma opción más veces de las indicadas en entradas separadas.';
$string['line'] = 'Línea';
$string['linksettings'] = 'Opciones del enlace al mensaje';
$string['linktoentry'] = 'Enlace a entrada';
$string['listformat'] = 'Formato de lista';
$string['listformat_comma'] = 'Separado por comas';
$string['listformat_commaspace'] = 'Separado por coma y espacios';
$string['listformat_newline'] = 'Separado por líneas';
$string['listformat_space'] = 'Separado por espacios';
$string['listformat_ul'] = 'Lista no ordenada';
$string['lock'] = 'Bloquear';
$string['manage'] = 'Gestionar';
$string['managemode'] = 'Modo de Gestión';
$string['manager'] = 'Gestor';
$string['mappingwarning'] = 'Todos los campos antiguos no mapeados a un campo nuevo se perderán, 
junto con los datos almacenados.';
$string['max'] = 'Máximo';
$string['maxsize'] = 'Tamaño Máximo';
$string['maxteamsize_error_form'] = '¡Se pueden seleccionar solamente un máximo de  {$a} miembros del Equipo!';
$string['me'] = 'Yo';
$string['mediafile'] = 'Archivo multimedia';
$string['mentor'] = 'Tutor';
$string['message_comment_created'] = 'Hola {$a->fullname},

el comentario siguiente fue añadido por {$a->senderprofilelink} a esta entrada: {$a->viewlink}:


{$a->commenttext}';
$string['message_entry_approved'] = 'Hola {$a->fullname},

el contenido en {$a->datalynxlink} ha sido aprobado por  {$a->senderprofilelink}.

La entrada siguiente ha sido aprobada: {$a->viewlink}.';
$string['message_entry_deleted'] = 'Hola {$a->fullname},

el contenido en {$a->datalynxlink} ha sido borrado por {$a->senderprofilelink}.

La entrada ha sido borrada: {$a->viewlink}.';
$string['message_entry_disapproved'] = 'Hola {$a->fullname},

el contenido en {$a->datalynxlink} ha sido rechazada por {$a->senderprofilelink}.

La entrada ha sido rechazada: {$a->viewlink}.';
$string['message_entry_updated'] = 'Hola {$a->fullname},

el contenido en {$a->datalynxlink} ha sido modificado por {$a->senderprofilelink}.

La entrada siguiente ha sido modificada: {$a->viewlink}.';
$string['message_rating_added'] = 'Registro de datos valoración añadidas';
$string['message_rating_updated'] = 'Registro de datos valoración actualizadas';
$string['message_team_updated'] = 'Hola {$a->fullname},

Se ha modificado la pertenencia a {$a->fieldname} por {$a->senderprofilelink}. 
Por favor, vaya a {$a->viewlink} para detalles.';
$string['messageprovider:event_entry_approved'] = 'Registro de datos entrada aprobadas';
$string['messageprovider:event_entry_created'] = 'Registro de datos entrada created';
$string['messageprovider:event_entry_deleted'] = 'Registro de datos entrada borradas';
$string['messageprovider:event_entry_disapproved'] = 'Registro de datos entrada disapproved';
$string['messageprovider:event_entry_updated'] = 'Registro de datos entrada actualizadas';
$string['messageprovider:event_rating_added'] = 'Registro de datos valoración añadidas';
$string['messageprovider:event_rating_updated'] = 'Registro de datos valoración actualizadas';
$string['messageprovider:event_team_updated'] = 'Registro de datos entrada team actualizadas';
$string['min'] = 'Mínimo';
$string['minteamsize'] = 'Minimum tamaño del grupo';
$string['minteamsize_error_form'] = 'You must select at least {$a} miembros del grupo!';
$string['minteamsize_error_value'] = 'Minimum tamaño del grupo cannot be greater than the máximo tamaño del grupo!';
$string['minteamsize_help'] = 'Enter the miminum allowed number de miembros del grupo here.';
$string['modearray'] = 'Modo de visualización';
$string['modearray_help'] = '\'To\' fecha is always considered when available until 23:59:59.';
$string['modulename'] = 'Registro de datos';
$string['modulename_help'] = 'El módulo Registro de datos es una variación de una Base de datos. 
Permite al Profesor definir una plantilla personalizada para la entrada de datos por los usuarios. 
Esas entradas pueden ser editadas, clasificadas, valoradas y calificadas, por el profesor o de forma colaborativa. 

Las entradas pueden combinar una variedad de tipos de datos (e.g. textos, números, imágenes, archivos, URLs etc.) 
en formatos también definidos de forma personalizada. 
De esta forma, el módulo puede ser usado para crear un amplio espectro de actividades o recursos que impliquen la recolección de datos rellenados por los particpantes.';
$string['modulenameplural'] = 'Registros de datos';
$string['more'] = 'Más';
$string['moreresults'] = '({$a}resultados más...)';
$string['movezipfailed'] = 'No se puede mover el ZIP';
$string['multiapprove'] = ' Aprobar ';
$string['multidelete'] = ' Borrar  ';
$string['multidownload'] = 'Descargar';
$string['multiduplicate'] = 'Duplicado';
$string['multiedit'] = ' Editar ';
$string['multiexport'] = 'Exportar';
$string['multipletags'] = '¡Múltiples etiquetas! Vista no guardada';
$string['multiselect'] = 'Multi-selección';
$string['multishare'] = 'Compartir';
$string['newbehavior'] = 'Nuevo comportamiento del campo';
$string['newfieldgroup'] = 'Nuevo conjunto';
$string['newfilterform'] = 'Nuevo Formulario de Filtro';
$string['newrenderer'] = 'Nuevo visualizador de campo';
$string['newvalue'] = 'Nuevo valor';
$string['newvalueallow'] = 'Permitir nuevos valores';
$string['noaccess'] = 'No tiene acceso a esta página';
$string['noautocompletion'] = 'No autocompletado';
$string['nocustomfilter'] = 'Error [nocustomfilter]. Contacte con el Administrador.';
$string['nodatalynxs'] = 'No se han encontrado Registros de datos';
$string['noedit'] = 'Not editable';
$string['noentries'] = 'No hay entradas que mostrar.';
$string['nomatch'] = '¡No hay extradas coincidentes!';
$string['nomatchingentries'] = 'No hay entradas coincidentes con el filtro seleccionado.';
$string['nomaximum'] = 'Sin máximo';
$string['nooptions'] = '¡Debe especificar al menos una opción!';
$string['nopermission'] = 'Usted no tiene permiso para ver entradas específicas.';
$string['nosuchentries'] = 'No hay entradas disponibles.';
$string['not'] = 'NO';
$string['notallowedtoeditentry'] = 'No está permitido editar esta entrada.';
$string['notapproved'] = 'Entrada no aprobada todavía.';
$string['noteditable'] = 'Cuando no editable';
$string['notemplate'] = 'Sin Plantilla';
$string['notificationenable'] = 'Activar notificaciones para';
$string['notifyteam'] = 'Regla de Notification';
$string['notifyteam_help'] = 'Seleccione la regla de notificación que será aplicada a todos las miembros del grupo especificado en este campo.';
$string['notifyteammembers'] = 'Notificar a miembros del grupo';
$string['notifyteammembers_help'] = 'Select this option to inform miembros del grupo de their membership status change.';
$string['notinjectivemap'] = 'Not an injective map';
$string['notopenyet'] = 'Esta actividad NO está disponible hasta el {$a}';
$string['notrequired'] = 'No requerido';
$string['notvisible'] = 'Cuando no visible';
$string['novalue'] = 'Cuando vacío';
$string['noviewsavailable'] = 'Sin vistas disponibles';
$string['numapprovedentries'] = 'Número de entradas aprobadas';
$string['numberrssarticles'] = 'Entradas RSS';
$string['numcharsallowed'] = 'Número de caracteres';
$string['numdeletedentries'] = 'Número de entradas borradas';
$string['numtotalentries'] = 'Número de created entradas';
$string['numvisits'] = 'Número de visitas';
$string['ondate'] = 'En fecha';
$string['option'] = 'Opción';
$string['optionaldescription'] = 'Descripción corta (opcional)';
$string['optionalfilename'] = 'Nombre de archivo (opcional)';
$string['or'] = 'O';
$string['other'] = 'Otro';
$string['otheruser'] = 'Otro usuario';
$string['overwrite'] = 'Sobrescribir';
$string['overwritesettings'] = 'Sobreescribir opciones actuales';
$string['page-mod-datalynx-x'] = 'Cualquier página de Registro de datos';
$string['pagesize'] = 'Entradas por página';
$string['pagingbar'] = 'Páginas';
$string['pagingnextslide'] = 'Siguiente';
$string['pagingpreviousslide'] = 'Anterior';
$string['participants'] = 'Participantes';
$string['period'] = 'Periodo';
$string['pleaseaddsome'] = 'Por favor, añada algunos datos debajo o {$a} para comenzar.';
$string['pluginadministration'] = 'Administración de Registro de datos';
$string['pluginname'] = 'Registro de datos';
$string['porttypeblank'] = 'Entradas vacías';
$string['porttypecsv'] = 'CSV';
$string['presetadd'] = 'Añadir paquetes';
$string['presetapply'] = 'Aplicar';
$string['presetavailableincourse'] = 'Paquetes del curso';
$string['presetavailableinsite'] = 'Paquetes del Sitio';
$string['presetchoose'] = 'seleccione un paquete prededinido';
$string['presetdata'] = 'con datos de usuarios';
$string['presetdataanon'] = 'con datos de usuario anonimizados';
$string['presetfaileddelete'] = '¡Error al borrar un paquete!';
$string['presetfromdatalynx'] = 'Hacer un paquete de este Registro de datos';
$string['presetfromfile'] = 'Cargar paquete desde archivo';
$string['presetimportsuccess'] = 'El paquete de ajustes se ha aplicado con éxito.';
$string['presetinfo'] = 'Guardar como un paquete publicará esta Vista. 
Otros usuarios podrán usar estos ajustes en sus Registros de datos.';
$string['presetmap'] = 'map fields';
$string['presetnodata'] = 'sin datos de usuarios';
$string['presetnodefinedfields'] = '¡El nuevo paquete no tiene campos!';
$string['presetnodefinedviews'] = '¡El nuevo paquete no tiene vistas!';
$string['presetnoneavailable'] = 'No hay paquetes disponibles para mostrar';
$string['presetplugin'] = 'Plugin';
$string['presetrefreshlist'] = 'Actualizar lista';
$string['presets'] = 'Paquetes';
$string['presetshare'] = 'Compartir';
$string['presetsharesuccess'] = 'Guardado adecuadament. Su paquete estará disponible en toda la plataforma.';
$string['presetsource'] = 'Origen del paquete';
$string['presetusestandard'] = 'Usar un paquete';
$string['privacy:metadata:datalynx_contents'] = 'Representa contenido de un campo que fue escrito en una instancia Datalynx.';
$string['privacy:metadata:datalynx_contents:content'] = 'Contenido';
$string['privacy:metadata:datalynx_contents:content1'] = 'Contenido adicional 1';
$string['privacy:metadata:datalynx_contents:content2'] = 'Contenido adicional 2';
$string['privacy:metadata:datalynx_contents:content3'] = 'Contenido adicional 3';
$string['privacy:metadata:datalynx_contents:content4'] = 'Contenido adicional 4';
$string['privacy:metadata:datalynx_contents:fieldid'] = 'ID de definición del campo';
$string['privacy:metadata:datalynx_entries'] = 'Representa entradas en una instancia Datalynx.';
$string['privacy:metadata:datalynx_entries:approved'] = 'Estado de aprobación';
$string['privacy:metadata:datalynx_entries:assessed'] = 'Mostrar si es que la instancia fue valorada';
$string['privacy:metadata:datalynx_entries:groupid'] = 'Grupo';
$string['privacy:metadata:datalynx_entries:status'] = 'Estado de esta entrada';
$string['privacy:metadata:datalynx_entries:timecreated'] = 'Hora de cuando fue creado el registro';
$string['privacy:metadata:datalynx_entries:timemodified'] = 'Hora de cuando fue modificado por última vez el registro';
$string['privacy:metadata:datalynx_entries:userid'] = 'Usuario que creó el registro';
$string['privacy:metadata:filepurpose'] = 'Archivo o imagen anexa a una instancia Datalynx.';
$string['random'] = 'Aleatorio';
$string['randomone'] = 'Una al azar';
$string['range'] = 'Intervalo';
$string['rate'] = 'Valorar';
$string['rating'] = 'Valoración';
$string['ratingmanual'] = 'Manual';
$string['ratingmethod'] = 'Método de valoración';
$string['ratingno'] = 'Sin valoraciones';
$string['ratingpublic'] = '{$a} puede ver las valoraciones de todos';
$string['ratingpublicnot'] = '{$a} puede ver sólo sus propias valoraciones';
$string['ratings'] = 'Valoraciones';
$string['ratingsaggregate'] = '{$a->value} ({$a->method} de {$a->count} valoraciones)';
$string['ratingsavg'] = 'Promedio de valoraciones';
$string['ratingscount'] = 'Número de valoraciones';
$string['ratingsmax'] = 'Mayor valoración';
$string['ratingsmin'] = 'Menor valoración';
$string['ratingsnone'] = '---';
$string['ratingssaved'] = 'Valoraciones guardadas';
$string['ratingssum'] = 'Suma de valoraciones';
$string['ratingsview'] = 'Ver valoraciones';
$string['ratingsviewrate'] = 'Ver y valorar';
$string['ratingvalue'] = 'Valoración';
$string['redirectsettings'] = 'Opciones de redirección al entregar';
$string['redirectsettings_help'] = 'Use estos campos para indicar a qué vista debe rediriguirse el navegador al dejar la vista de edición.';
$string['redirectto'] = 'Vista de destino';
$string['reference'] = 'Referencia';
$string['referencefield'] = 'Campo de Referencia';
$string['referencefield_help'] = 'Select a field to serve as a duplicar prevention field. This will skip creating entradas for users who already have an aprobadas entrada with the same field value as the one being aprobadas.';
$string['renameoption'] = 'Renombrar a:';
$string['renderer'] = 'Visualizador';
$string['rendereradd'] = 'Añadir visualizador';
$string['renderers'] = 'Visualizadores';
$string['requireapproval'] = '¿Requiere aprobación?';
$string['required'] = 'Obligatorio';
$string['requiredall'] = 'todas requeridas';
$string['requirednotall'] = 'no todas requeridas';
$string['resetsettings'] = 'Reiniciar filtros';
$string['returntoimport'] = 'Volver a Importar';
$string['rssglobaldisabled'] = 'Desactivada. Ver opciones de configuración globales.';
$string['rsshowmany'] = '(nº de entradas a mostrar, 0 para desactivar RSS)';
$string['rsstemplate'] = 'Plantilla RSS';
$string['rsstitletemplate'] = 'Título de Plantilla RSS';
$string['rule'] = 'regla';
$string['ruleaction'] = 'Acción';
$string['ruleadd'] = 'Añadir una regla';
$string['rulecancel'] = 'Cancelar regla';
$string['rulecondition'] = 'Condición';
$string['rulecreate'] = 'Crear nueva regla';
$string['ruledenydelete'] = 'Impedir borrado de entrada';
$string['ruledenyedit'] = 'Impedir edición de entrada';
$string['ruledenyview'] = 'Ocultar la entrada a todos';
$string['ruledenyviewbyother'] = 'Ocultar la entrada a todos salvo autor';
$string['ruledescription'] = 'Descripción';
$string['ruleedit'] = 'Editando \'{$a}\'';
$string['ruleenabled'] = 'Habilitada';
$string['rulename'] = 'Auto-enlazado de Registro de datos';
$string['rulenew'] = 'Nueva regla {$a}';
$string['rulenoneforaction'] = 'No se encontraron reglas para la acción requerida';
$string['rules'] = 'Reglas';
$string['rulesadded'] = '{$a} regla(s) añadidas';
$string['rulesave'] = 'Guardar regla';
$string['rulesconfirmdelete'] = 'Ha solicitado borrar {$a} regla(s). ¿Desea continuar?';
$string['rulesconfirmduplicate'] = 'Ha solicitado duplicar {$a} regla(s). ¿Desea continuar?';
$string['rulesdeleted'] = '{$a} regla(s) borradas';
$string['rulesduplicated'] = '{$a} regla(s) duplicadas';
$string['rulesmax'] = 'Máximo reglas';
$string['rulesnonedefined'] = 'No hay reglas definidas';
$string['rulesnoneindatalynx'] = 'No hay reglas definidas en este Registro de datos.';
$string['rulesupdated'] = '{$a} regla(s) actualizadas';
$string['ruleupdate'] = 'Actualizar un existentes regla';
$string['saveasstandardtags'] = 'Guardar etiquetas como estándard para ser sugeridas al añadir o editar una entrada?';
$string['savecontinue'] = 'Guardar y continuar';
$string['search'] = 'Buscar';
$string['selectuser'] = 'Seleccionar usuario...';
$string['sendinratings'] = 'Enviar mis últimas valoraciones';
$string['separateentries'] = 'cada entrada en archivos separados';
$string['separateparticipants'] = 'Separate participants';
$string['setdefault'] = 'Establecer como vista predefinida';
$string['setedit'] = 'Establecer como vista de edición';
$string['setmore'] = 'Establecer como vista detallada';
$string['settings'] = 'Opciones';
$string['showall'] = 'Mostrar todas las entradas';
$string['shownothing'] = 'No mostrar nada';
$string['singleedit'] = 'E';
$string['singlemore'] = 'M';
$string['sortable'] = 'ordenable';
$string['spreadsheettype'] = 'Tipo Hoja-de-cálculo';
$string['statistics'] = 'Estadísticas';
$string['statisticsfor'] = 'Estadísticas de \'{$a}\'';
$string['status'] = 'Estado';
$string['status_draft'] = 'Borrador';
$string['status_finalsubmission'] = 'Entrega final';
$string['status_notcreated'] = 'No creado';
$string['status_submission'] = 'Entregado';
$string['statusrequired'] = '¡Debe configurarse el Estado!';
$string['student'] = 'Estudiante';
$string['submission'] = 'Entrega';
$string['submissionsinpopup'] = 'Entregas en ventana emergente';
$string['submissionsview'] = 'Vista de Entregas';
$string['subplugintype_datalynxfield'] = 'Tipo de campo de Registro de datos';
$string['subplugintype_datalynxfield_plural'] = 'Tipos de campo de Registro de datos';
$string['subplugintype_datalynxrule'] = 'Tipo de regla de Registro de datos';
$string['subplugintype_datalynxrule_plural'] = 'Tipos de regla Registro de datos';
$string['subplugintype_datalynxtool'] = 'Tipo de herramienta de Registro de datos';
$string['subplugintype_datalynxtool_plural'] = 'Tipos de herramienta Registro de datos';
$string['subplugintype_datalynxview'] = 'Tipo de vista Registro de datos';
$string['subplugintype_datalynxview_plural'] = 'Tipos de vista Registro de datos';
$string['subscribe'] = 'Subscribir';
$string['tagarea_datalynx_contents'] = 'Entradas de Registro de datos';
$string['tagcollection_datalynx'] = 'Etiquetas de Registro de datos';
$string['tagproperties'] = '{$a->tagtype} propiedades de etiqueta: {$a->tagname}';
$string['targetview_default'] = '(Por defecto)';
$string['targetview_edit'] = '(Editar)';
$string['targetview_more'] = '(Más)';
$string['targetview_this'] = '(Esta vista)';
$string['targetview_this_new'] = 'Esta vista (Nueva)';
$string['targetviewforroles'] = 'Enlazar vistas de destino para roles';
$string['teacher'] = 'Profesor';
$string['teachersandstudents'] = '{$a->teachers} y {$a->students}';
$string['teamfield'] = 'Campo de Equipo';
$string['teamfield_help'] = 'Marcar para señalar como un campo de Equipo. 
Cuando se aprueba una entrada para un Equipo concreto, esa entrada es copiada y 
asignada a cada miembro individual del equipo.  

Solo puede definirse como Campo de Equipo a un único campo en cada instancia separada de Registro de datos.';
$string['teammembers'] = 'Miembros del Equipo';
$string['teammemberselectmultiple'] = 'Una persona solo puede ser seleccionada una vez como miembro del Equipo.';
$string['teams'] = 'Equipos';
$string['teamsize'] = 'Máximo tamaño del Equipo';
$string['teamsize_error_required'] = 'Campo obligatorio';
$string['teamsize_error_value'] = 'El valor debe ser un número entero positivo';
$string['teamsize_help'] = 'Especifique el máximo tamaño del grupoo. Debe ser un número entero positivo.';
$string['textbox'] = 'Caja de texto';
$string['textfield'] = 'Campo de texto';
$string['textfield_help'] = 'Campo de texto para autocompletado.';
$string['textfieldvalues'] = 'Valores del campo de texto';
$string['thisdatalynx'] = 'Esta instancia  Registro de datos instance';
$string['thisfield'] = 'Este campo';
$string['time_field_required'] = '{$a} field is requeridas!';
$string['timecreated'] = 'Fecha de creación';
$string['timemodified'] = 'Fecha de modificación';
$string['timestring0'] = 'de {$a->from} a {$a->to}';
$string['timestring1'] = 'en {$a->from}';
$string['timestring2'] = 'hasta {$a->to}';
$string['timestring3'] = 'de {$a->from} a ahora ({$a->now})';
$string['timestring4'] = 'hasta ahora ({$a->now})';
$string['todatalynx'] = 'a este Registro de datos.';
$string['todate'] = 'Hasta fecha';
$string['toolnoneindatalynx'] = 'No hay herramientas definidas en este Registro de datos.';
$string['toolrun'] = 'Ejecutar';
$string['tools'] = 'Herramientas';
$string['triggeringevent'] = 'Evento disparador';
$string['trusttext'] = 'Texto de confianza';
$string['type'] = 'Tipo';
$string['unique'] = 'Único';
$string['unique_required'] = '¡Se requiere texto único! Este texto ya se ha utilizado.';
$string['unlock'] = 'Desbloquear';
$string['unsubscribe'] = 'Desubscribir';
$string['updateexisting'] = 'Actualizar existentes';
$string['updatefield'] = 'Actualizar un campo existente';
$string['updateview'] = 'Actualizar una vista existente';
$string['uploadfile'] = 'Archivo a importar';
$string['uploadtext'] = 'Texto a importar';
$string['urlclass'] = 'CSS classes';
$string['urltarget'] = 'Atributo \'target\'';
$string['user_can_add_self'] = 'Los usuario pueden añadirse por si mismos';
$string['user_can_add_self_help'] = 'Marcar para permitir que el usuario propietario de la entrada 
se añada directamente al Equipo de la entrada.';
$string['userfields'] = 'Campos definidos por usuario';
$string['userinfo'] = 'Info. de usuario';
$string['useristeammember'] = 'El usuario es miembro del Equipo';
$string['userpref'] = 'Preferencias de usuario';
$string['usersubmissions'] = 'entregas de usuario';
$string['usersubmissionsinpopup'] = 'entregas en ventana emergente';
$string['usersubmissionsview'] = 'Vista de entrega de usuario';
$string['vertical'] = 'Vertical';
$string['view'] = 'Vista';
$string['viewadd'] = 'Añadir una Vista';
$string['viewcharactertags'] = 'Etiquetas de Carácter';
$string['viewcreate'] = 'Crear nueva Vista';
$string['viewcurrent'] = 'Vista actual';
$string['viewcustomdays'] = 'Intervalo de actualización: días';
$string['viewcustomhours'] = 'Intervalo de actualización: horas';
$string['viewcustomminutes'] = 'Intervalo de actualización: minutos';
$string['viewdescription'] = 'Descripción de la Vista';
$string['viewedit'] = 'Editando \'{$a}\'';
$string['vieweditthis'] = 'Editar vista';
$string['viewfieldtags'] = 'Marcas del campo';
$string['viewfilter'] = 'Filtro';
$string['viewforedit'] = 'Vista para \'editar\'';
$string['viewformore'] = 'Vista para \'más\'';
$string['viewfromdate'] = 'Ver desde';
$string['viewgeneral'] = 'Ver opciones genéricas';
$string['viewgeneral_help'] = 'Ver opciones genéricas';
$string['viewgeneraltags'] = 'Etiquetas genéricas';
$string['viewgroupby'] = 'Agrupar por';
$string['viewinterval'] = 'Cuándo actualizar el contenido';
$string['viewintervalsettings'] = 'Opciones de intervalo';
$string['viewlistfooter'] = 'Pie de lista';
$string['viewlistheader'] = 'Encabezado de lista';
$string['viewmultiplefieldgroups'] = 'No puede usar más de un campos de grupo.';
$string['viewname'] = 'Nombre de la vista';
$string['viewnew'] = 'Nueva vista {$a}';
$string['viewnodefault'] = 'No se ha definido una vista por defecto. 
Seleccione una de las vistas en la lista {$a} como la vista pedefinida.';
$string['viewnoneforaction'] = 'No hay vistas seleccionadas para la acción requerida';
$string['viewnoneindatalynx'] = 'No hay vistas definidas en este Registro de datos.';
$string['viewoptions'] = 'Ver opciones';
$string['viewpagingfield'] = 'Campo de paginado';
$string['viewperpage'] = 'Nº por página';
$string['viewrepeatedfields'] = 'No se puede usar el campo {$a} más de una vez.';
$string['viewresettodefault'] = 'Volver al predefinido';
$string['viewreturntolist'] = 'Volver al listado';
$string['views'] = 'Vistas';
$string['viewsadded'] = 'Vista añadida';
$string['viewsconfirmdelete'] = 'Ha solicitado borrar {$a} vista(s). ¿Desea continuar?';
$string['viewsconfirmduplicate'] = 'Ha solicitado duplicar {$a} vista(s). ¿Desea continuar?';
$string['viewsdeleted'] = 'Vista eliminada';
$string['viewsectionpos'] = 'Posición de sección';
$string['viewslidepaging'] = 'Paginado';
$string['viewsmax'] = 'Máximo de vistas';
$string['viewsupdated'] = 'Vista actualizada';
$string['viewtemplate'] = 'Ver Plantilla';
$string['viewtemplate_help'] = 'Ver Plantilla';
$string['viewtodate'] = 'Accesible por';
$string['viewvisibility'] = 'Visibilidad';
$string['visibility'] = 'Visibilidad';
$string['visible_1'] = 'Administrador';
$string['visible_2'] = 'Profesor';
$string['visible_4'] = 'Estudiante';
$string['visible_8'] = 'Invitado';
$string['visibleto'] = 'Visible para';
$string['wrongdataid'] = 'Se ha indicado una ID de Registro de datos incorrecta';
