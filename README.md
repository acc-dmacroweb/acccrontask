# ACC Cron Task - Cron Task Management Module for PrestaShop

## Description

PrestaShop module that allows you to manage custom cron tasks with a modern and updated interface. Fully compatible with PrestaShop 1.7, 8.0 and 9.0, with automatic adaptation to each version's requirements.

## Features

- ✅ Modern and updated interface
- ✅ Complete cron task management
- ✅ Configurable fields: Name, URL, Frequency (Every hour, Daily, Weekly, Monthly, Yearly), Minute (0-59), Hour (0-23), Day of week, Day of month, Month
- ✅ Listing with all columns: Name, URL, Hour, Minute, Month, Day of week, Last execution
- ✅ Action buttons: Enable/Disable, Edit, Delete (in dropdown)
- ✅ Automatic cron command generation for the server
- ✅ Fully compatible with PrestaShop 1.7, 8.0 and 9.0 with automatic adaptation

## Installation

1. Upload the `acccrontask` folder to the `modules/` directory of your PrestaShop installation
2. Go to PrestaShop Back Office
3. Navigate to Modules > Module Manager
4. Search for "ACC Cron Task"
5. Click on "Install"

**Note for PrestaShop 9.0**: If you encounter any routing issues after installation, try accessing the module configuration page once. The module will automatically update its Tab configuration for PrestaShop 9 compatibility.

## Usage

### Create a new cron task

1. Go to Tools > ACC Cron Task
2. Click on "Add new task"
3. Fill in the fields:
   - **Name**: Descriptive name for the task
   - **URL**: Complete URL to execute
   - **Frequency**: Every hour, Daily, Weekly, Monthly, or Yearly
   - **Minute**: Execution minute (0-59)
   - **Hour**: Execution time (0-23)
   - **Day of week**: Optional (select a specific day for weekly frequency, or leave empty for daily tasks)
   - **Day of month**: Optional (1-31, required for monthly/yearly frequencies)
   - **Month**: Optional (select a specific month for yearly frequency)
   - **Active**: Enable or disable the task
4. Click on "Save"

### Generate Cron command for the server

1. In the task list, click on "Generate Cron command"
2. Copy the generated command
3. Add it to your server crontab using `crontab -e`

The generated command will have the format:
```
* * * * * curl -k "https://yourdomain.com/modules/acccrontask/controllers/front/cron.php?token=TOKEN"
```

**Note:** The command runs every minute. The module internally checks which tasks should be executed based on their configured schedule (hour, minute, day, month, etc.). The token in the URL is a fixed security token generated from your PrestaShop installation's secret key.

## Module Structure

```
acccrontask/
├── acccrontask.php (Main module file)
├── classes/
│   └── AccCronTaskModel.php (Data model)
├── controllers/
│   ├── admin/
│   │   └── AdminAccCronTaskController.php (Admin controller)
│   └── front/
│       └── cron.php (Front controller for execution)
├── views/
│   └── templates/
│       └── admin/
│           ├── cron_command.tpl (Template to display cron command)
│           ├── form_js.tpl (JavaScript for form behavior)
│           ├── list_override.tpl (Custom listing template)
│           └── list.tpl (Standard listing template)
└── index.php (Security file)
```

## Requirements

- PrestaShop 1.7.0.0 or higher (tested with 1.7, 8.0 and 9.0)
- PHP 7.1 or higher
- PHP cURL extension (to execute cron tasks)

## Compatibility

This module is fully compatible with:
- **PrestaShop 1.7.x**: Works with the standard controller routing system
- **PrestaShop 8.0.x**: Works with the standard controller routing system (same as 1.7)
- **PrestaShop 9.0.x**: Automatically adapts to the new routing system requirements

The module automatically detects the PrestaShop version and adjusts its behavior accordingly. No additional configuration is needed.

### Version-Specific Features

- **PrestaShop 1.7 & 8.0**: Uses standard Tab configuration without route_name
- **PrestaShop 9.0**: Automatically handles routing system requirements
- **All versions**: Uses module-based translations for full compatibility

## Technical Details

### Translation System
The module uses PrestaShop's module-based translation system (`$this->module->l()`) for full compatibility across all PrestaShop versions, including PrestaShop 9 where controller-based translations are no longer available.

### Routing System
- **PrestaShop 1.7 & 8.0**: Uses standard Tab-based routing
- **PrestaShop 9.0**: Automatically adapts to the new routing requirements without manual configuration

### Security
The module uses a fixed security token generated from your PrestaShop installation's secret key. This token remains constant and doesn't depend on user sessions, making it ideal for cron job execution.

## Troubleshooting

### PrestaShop 9.0 - Controller Not Found Error
If you see "Controller AdminAccCronTask not found" in PrestaShop 9.0:
1. Access the module configuration page (Modules > Module Manager > ACC Cron Task > Configure)
2. The module will automatically update its Tab configuration
3. Clear PrestaShop cache (Advanced Parameters > Performance > Clear cache)
4. Try accessing the module again

### Module Installation Issues
- Ensure you have the correct file permissions
- Clear PrestaShop cache after installation
- Check that all files were uploaded correctly

## Support

For support or inquiries, contact the developer.

## Changelog

### Version 1.0.0
- Initial release
- Full compatibility with PrestaShop 1.7, 8.0 and 9.0
- Automatic version detection and adaptation
- Module-based translation system for PS 9 compatibility
- Automatic Tab configuration for PS 9 routing system

## License

Academic Free License (AFL 3.0)

