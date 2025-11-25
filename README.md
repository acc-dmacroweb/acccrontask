# ACC Cron Task - Cron Task Management Module for PrestaShop

## Description

PrestaShop module that allows you to manage custom cron tasks with a modern and updated interface. Compatible with PrestaShop 1.7, 8.x and 9.x.

## Features

- ✅ Modern and updated interface
- ✅ Complete cron task management
- ✅ Configurable fields: Name, URL, Frequency (Every hour, Daily, Weekly, Monthly, Yearly), Minute (0-59), Hour (0-23), Day of week, Day of month, Month
- ✅ Listing with all columns: Name, URL, Hour, Minute, Month, Day of week, Last execution
- ✅ Action buttons: Enable/Disable, Edit, Delete (in dropdown)
- ✅ Automatic cron command generation for the server
- ✅ Compatible with PrestaShop 1.7, 8.x and 9.x

## Installation

1. Upload the `acccrontask` folder to the `modules/` directory of your PrestaShop installation
2. Go to PrestaShop Back Office
3. Navigate to Modules > Module Manager
4. Search for "ACC Cron Task"
5. Click on "Install"

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

- PrestaShop 1.7.0.0 or higher
- PHP 7.1 or higher
- PHP cURL extension (to execute cron tasks)

## Support

For support or inquiries, contact the developer.

## License

Academic Free License (AFL 3.0)

