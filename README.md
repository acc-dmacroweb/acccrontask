# ACC Cron Task - Módulo de Gestión de Tareas Cron para PrestaShop

## Descripción

Módulo para PrestaShop que permite gestionar tareas cron personalizadas con una interfaz moderna y actualizada. Compatible con PrestaShop 1.7, 8.x y 9.x.

## Características

- ✅ Interfaz moderna y actualizada
- ✅ Gestión completa de tareas cron
- ✅ Campos configurables: Nombre, URL, Frecuencia, Minutos (de 5 en 5), Día de la semana, Día del mes, Mes
- ✅ Listado con todas las columnas: Nombre, URL, Hora, Minuto, Mes, Día de la semana, Última ejecución
- ✅ Botones de acción: Activar/Desactivar, Modificar, Eliminar (en desplegable)
- ✅ Generación automática de comando cron para el servidor
- ✅ Compatible con PrestaShop 1.7, 8.x y 9.x

## Instalación

1. Sube la carpeta `acccrontask` al directorio `modules/` de tu instalación de PrestaShop
2. Ve al Back Office de PrestaShop
3. Navega a Módulos > Gestor de módulos
4. Busca "ACC Cron Task"
5. Haz clic en "Instalar"

## Uso

### Crear una nueva tarea cron

1. Ve a Herramientas > ACC Cron Task
2. Haz clic en "Añadir nueva tarea"
3. Completa los campos:
   - **Nombre**: Nombre descriptivo de la tarea
   - **URL**: URL completa a ejecutar
   - **Frecuencia día**: Diario, Semanal, Mensual, Anual o Personalizado
   - **Minuto**: Selecciona el minuto (de 5 en 5)
   - **Hora**: Hora de ejecución (0-23)
   - **Día de la semana**: Opcional (dejar en "Todos" para todos los días)
   - **Día del mes**: Opcional (1-31, -1 para todos)
   - **Mes**: Opcional (dejar en "Todos" para todos los meses)
   - **Activo**: Activar o desactivar la tarea
4. Haz clic en "Guardar"

### Generar comando Cron para el servidor

1. En el listado de tareas, haz clic en "Generar comando Cron"
2. Copia el comando generado
3. Añádelo a tu crontab del servidor usando `crontab -e`

El comando generado tendrá el formato:
```
0 * * * * curl -k "https://tudominio.com/modules/acccrontask/controllers/front/cron.php?token=TOKEN"
```

## Estructura del módulo

```
acccrontask/
├── acccrontask.php (Archivo principal del módulo)
├── classes/
│   └── AccCronTaskModel.php (Modelo de datos)
├── controllers/
│   ├── admin/
│   │   └── AdminAccCronTaskController.php (Controlador admin)
│   └── front/
│       └── cron.php (Controlador front para ejecución)
├── views/
│   └── templates/
│       └── admin/
│           ├── cron_command.tpl (Template para mostrar comando cron)
│           └── list_override.tpl (Template personalizado del listado)
└── index.php (Archivo de seguridad)
```

## Requisitos

- PrestaShop 1.7.0.0 o superior
- PHP 7.1 o superior
- Extensión cURL de PHP (para ejecutar las tareas cron)

## Soporte

Para soporte o consultas, contacta con el desarrollador.

## Licencia

Academic Free License (AFL 3.0)

