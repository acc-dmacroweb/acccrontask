# Cron Tasks Manager PRO

[![PrestaShop](https://img.shields.io/badge/PrestaShop-1.7%20%7C%208.0%20%7C%209.0-orange.svg)](https://www.prestashop.com/)
[![PHP](https://img.shields.io/badge/PHP-7.1%2B-blue.svg)](https://www.php.net/)
[![License](https://img.shields.io/badge/License-AFL%203.0-green.svg)](http://opensource.org/licenses/afl-3.0.php)

## 📋 Description

**Cron Tasks Manager PRO** is a powerful PrestaShop module that allows you to manage custom cron tasks with a modern and intuitive interface. This module provides complete control over scheduled tasks, supporting multiple execution frequencies and automatic cron command generation for server integration.

### Key Benefits

- **Easy Management**: Create, edit, and manage cron tasks directly from PrestaShop Back Office
- **Multiple Frequencies**: Support for hourly, daily, weekly, monthly, yearly, and custom Unix-style cron schedules
- **Automatic Command Generation**: Generate ready-to-use cron commands for your server
- **Full Compatibility**: Works seamlessly with PrestaShop 1.7, 8.0, and 9.0
- **Secure Execution**: Fixed security token system for safe cron job execution
- **Modern Interface**: Responsive design with Bootstrap 5 styling

## ✨ Features

### Core Features

- ✅ **Complete Task Management**
  - Create, edit, delete, and manage cron tasks
  - Enable/disable tasks without deletion
  - View execution history and last execution time

- ✅ **Flexible Scheduling**
  - Every hour (configurable minute)
  - Daily (specific hour and minute)
  - Weekly (day of week, hour, minute)
  - Monthly (day of month, hour, minute)
  - Yearly (month, day, hour, minute)
  - Custom Unix-style cron format (advanced users)

- ✅ **User-Friendly Interface**
  - Modern responsive design
  - Intuitive form with conditional field display
  - Clear task listing with all relevant information
  - Quick action buttons (Edit, Execute, Delete)

- ✅ **Server Integration**
  - Automatic cron command generation
  - Fixed security token (session-independent)
  - Ready-to-use curl commands
  - Complete URL generation for execution

- ✅ **Advanced Features**
  - Unix-style cron format support
  - Automatic cron_unix_style generation from individual fields
  - Validation of cron expressions
  - Execution status tracking

## 🔧 Requirements

### System Requirements

- **PrestaShop**: 1.7.0.0 or higher (tested with 1.7.x, 8.0.x, and 9.0.x)
- **PHP**: 7.1 or higher
- **PHP Extensions**:
  - cURL (required for task execution)
  - MySQL/MariaDB

### Server Requirements

- Access to server crontab (for automatic task execution)
- cURL command available on server
- Write permissions to module directory

## 📦 Installation

### Method 1: Via PrestaShop Back Office (Recommended)

1. **Download the module**
   - Download the `crontasksmanagerpro.zip` file
   - Extract the archive

2. **Upload to PrestaShop**
   - Go to your PrestaShop Back Office
   - Navigate to **Modules > Module Manager**
   - Click on **"Upload a module"** button
   - Select the `crontasksmanagerpro.zip` file
   - Click **"Upload this module"**

3. **Install the module**
   - Search for **"Cron Tasks Manager PRO"** in the module list
   - Click on **"Install"**
   - Wait for the installation to complete

### Method 2: Manual Installation

1. **Extract the module**
   - Extract the `crontasksmanagerpro` folder from the archive

2. **Upload files**
   - Upload the `crontasksmanagerpro` folder to your PrestaShop `modules/` directory
   - Ensure all files are uploaded correctly

3. **Install via Back Office**
   - Go to **Modules > Module Manager**
   - Search for **"Cron Tasks Manager PRO"**
   - Click on **"Install"**

### Post-Installation

After installation, the module will:
- Create the necessary database table
- Register the admin controller in Tools menu
- Set up the required Tab configuration

**Note for PrestaShop 9.0**: If you encounter routing issues, access the module configuration page once. The module will automatically update its Tab configuration for PrestaShop 9 compatibility.

## 🚀 Usage

### Accessing the Module

1. Go to **Tools > Cron Tasks Manager PRO** in your PrestaShop Back Office
2. You will see the list of all cron tasks (empty on first use)

### Creating a New Cron Task

1. Click on **"Add new task"** button
2. Fill in the form fields:

   **Required Fields:**
   - **Name**: Descriptive name for your task (e.g., "Daily Backup", "Hourly Sync")
   - **URL**: Complete URL to execute (must be accessible via HTTP/HTTPS)
   - **Frequency**: Select the execution frequency
   - **Minute**: Execution minute (0-59)

   **Optional Fields (depending on frequency):**
   - **Hour**: Execution hour (0-23) - required for Daily, Weekly, Monthly, Yearly
   - **Day of week**: Day of the week (0=Sunday, 6=Saturday) - required for Weekly
   - **Day of month**: Day of the month (1-31) - required for Monthly and Yearly
   - **Month**: Month (1-12) - required for Yearly
   - **Cron Unix Style**: Advanced Unix-style cron format (e.g., "0 2 * * *" for daily at 2:00 AM)
   - **Active**: Enable/disable the task

3. Click **"Save"** to create the task

### Frequency Options Explained

- **Every hour**: Executes every hour at the specified minute (e.g., every hour at minute 0)
- **Daily**: Executes once per day at the specified hour and minute
- **Weekly**: Executes once per week on the specified day, hour, and minute
- **Monthly**: Executes once per month on the specified day, hour, and minute
- **Yearly**: Executes once per year on the specified month, day, hour, and minute
- **Cron Unix Style**: Advanced format for complex schedules (e.g., "*/5 * * * *" for every 5 minutes)

### Executing Tasks Manually

1. In the task list, find the task you want to execute
2. Click on the **"Execute"** button (green button with play icon)
3. Confirm the execution
4. The task will be executed immediately and the last execution time will be updated

### Generating Cron Command for Server

1. In the task list, click on **"Generate Cron command"** button (top toolbar)
2. A page will display:
   - The complete cron command to add to your server crontab
   - The execution URL with security token
3. Copy the cron command
4. Add it to your server crontab:
   ```bash
   crontab -e
   ```
5. Paste the command and save

**Example generated command:**
```bash
* * * * * curl -k "https://yourdomain.com/modules/crontasksmanagerpro/controllers/front/cron.php?token=YOUR_SECURITY_TOKEN"
```

**Important Notes:**
- The command runs every minute, but the module internally checks which tasks should execute
- The security token is fixed and generated from your PrestaShop installation's secret key
- The token remains constant and doesn't depend on user sessions
- Use `-k` flag to skip SSL certificate verification (remove if you have valid SSL)

### Managing Tasks

- **Edit**: Click the blue "Edit" button to modify a task
- **Delete**: Click the red "Delete" button to remove a task permanently
- **Enable/Disable**: Toggle the "Active" switch in the task list
- **View Details**: All task information is displayed in the listing table

## 🏗️ Module Structure

```
crontasksmanagerpro/
├── crontasksmanagerpro.php              # Main module file
├── index.php                         # Security file
├── logo.png                          # Module logo
├── classes/
│   └── crontasksmanagerProModel.php    # Data model (ObjectModel)
├── controllers/
│   ├── admin/
│   │   └── AdmincrontasksmanagerProController.php  # Admin controller
│   └── front/
│       └── cron.php                  # Front controller for cron execution
├── views/
│   ├── css/
│   │   └── list.css                  # Custom styles for listing
│   ├── js/
│   │   └── list.js                   # JavaScript for listing functionality
│   └── templates/
│       └── admin/
│           ├── cron_command.tpl       # Template for cron command display
│           ├── form_js.tpl           # JavaScript for form behavior
│           ├── list_override.tpl     # Custom listing template
│           └── list.tpl              # Standard listing template
└── README.md                         # This file
```

## 🔒 Security

**Security Features:**
- Token is unique per PrestaShop installation
- Token remains constant (doesn't change with sessions)
- Token is required for all cron executions
- Invalid token requests return 403 Forbidden

### Best Practices

1. **HTTPS**: Always use HTTPS for cron execution URLs
2. **Firewall**: Consider restricting access to the cron.php file by IP
3. **Token Protection**: Never share your cron execution URL publicly
4. **Regular Updates**: Keep PrestaShop and the module updated

## 🔌 Compatibility

### PrestaShop Versions

| Version | Status | Notes |
|---------|--------|-------|
| 1.7.x   | ✅ Fully Compatible | Standard Tab routing |
| 8.0.x   | ✅ Fully Compatible | Standard Tab routing (same as 1.7) |
| 9.0.x   | ✅ Fully Compatible | Automatic routing adaptation |

### Automatic Version Detection

The module automatically detects the PrestaShop version and adapts:
- **PrestaShop 1.7 & 8.0**: Uses standard Tab configuration
- **PrestaShop 9.0**: Automatically handles new routing requirements
- **All versions**: Uses module-based translations for compatibility

### Technical Compatibility

- **Translation System**: Uses `$this->module->l()` for full PS 9 compatibility
- **Routing**: Automatic Tab configuration for PS 9 routing system
- **Database**: Compatible with MySQL 5.7+ and MariaDB 10.2+
- **PHP**: Compatible with PHP 7.1 through PHP 8.2

## 🛠️ Technical Details

### Database Structure

The module creates a table `ps_crontasksmanagerpro` with the following structure:

- `id_crontasksmanagerpro` (Primary Key)
- `name` (Task name)
- `url` (Execution URL)
- `frequency_day` (Frequency type: 0=Daily, 1=Weekly, 2=Monthly, 3=Yearly, 5=Hourly, 6=Unix Style)
- `minute` (0-59)
- `hour` (0-23)
- `day_of_week` (0-6, -1 for not applicable)
- `day_of_month` (1-31, -1 for not applicable)
- `month` (1-12, -1 for not applicable)
- `cron_unix_style` (Unix-style cron format)
- `active` (Boolean)
- `last_execution` (DateTime, nullable)
- `date_add` (DateTime)
- `date_upd` (DateTime)

### Execution Logic

1. **Cron Command**: Runs every minute via server crontab
2. **Module Check**: Module checks all active tasks
3. **Schedule Validation**: Validates if task should execute based on:
   - Current time vs. configured schedule
   - Time since last execution
   - Task frequency settings
4. **Execution**: Executes matching tasks via cURL
5. **Logging**: Updates `last_execution` timestamp

### Unix-Style Cron Format

The module supports standard Unix cron format:
```
* * * * *
│ │ │ │ │
│ │ │ │ └── Day of week (0-6, 0=Sunday)
│ │ │ └──── Month (1-12)
│ │ └────── Day of month (1-31)
│ └──────── Hour (0-23)
└────────── Minute (0-59)
```

**Examples:**
- `0 2 * * *` - Daily at 2:00 AM
- `*/5 * * * *` - Every 5 minutes
- `0 0 1 * *` - First day of every month at midnight
- `0 9 * * 1` - Every Monday at 9:00 AM

## 🐛 Troubleshooting

### Common Issues

#### Issue: "Controller Admincrontasksmanagerpro not found" (PrestaShop 9.0)

**Solution:**
1. Go to **Modules > Module Manager > Cron Tasks Manager PRO > Configure**
2. The module will automatically update its Tab configuration
3. Clear PrestaShop cache: **Advanced Parameters > Performance > Clear cache**
4. Try accessing the module again

#### Issue: Tasks not executing

**Possible Causes & Solutions:**
1. **Cron command not set up on server**
   - Verify the cron command is in your server crontab
   - Check cron service is running: `service cron status`

2. **Invalid URL**
   - Verify the URL is accessible
   - Test the URL manually in a browser
   - Check for SSL certificate issues

3. **Task is disabled**
   - Check the "Active" status in the task list
   - Enable the task if it's disabled

4. **Schedule not matching**
   - Verify the current time matches the configured schedule
   - Check timezone settings

#### Issue: Module installation fails

**Solutions:**
1. Check file permissions (folders: 755, files: 644)
2. Verify PHP version meets requirements
3. Check PrestaShop error logs
4. Ensure database user has CREATE TABLE permissions

#### Issue: "Invalid token" error

**Solutions:**
1. Regenerate the cron command from the module
2. Verify the token in the URL matches the generated one
3. Check that `_COOKIE_KEY_` and `_COOKIE_IV_` haven't changed

### Debug Mode

To enable debug mode for cron execution:
1. Add `&debug=1` to the cron URL
2. Access the URL in a browser
3. Check the detailed error message

**Example:**
```
https://yourdomain.com/modules/crontasksmanagerpro/controllers/front/cron.php?token=YOUR_TOKEN&debug=1
```

## 📝 Changelog

### Version 1.0.0 (Initial Release)

**Features:**
- Initial release of Cron Tasks Manager PRO
- Full compatibility with PrestaShop 1.7, 8.0, and 9.0
- Automatic version detection and adaptation
- Module-based translation system for PS 9 compatibility
- Automatic Tab configuration for PS 9 routing system
- Support for multiple execution frequencies
- Unix-style cron format support
- Automatic cron command generation
- Fixed security token system
- Modern responsive interface
- Manual task execution
- Execution history tracking

**Technical:**
- PSR-12 coding standards compliance
- PrestaShop best practices implementation
- Secure token-based authentication
- Efficient database structure
- Optimized execution logic

## 📞 Support

### Getting Help

For support, bug reports, or feature requests:

1. **Check Documentation**: Review this README and troubleshooting section
2. **Check Logs**: Review PrestaShop error logs for detailed error messages
3. **Contact Developer**: Reach out through your preferred channel

### Reporting Issues

When reporting issues, please include:
- PrestaShop version
- PHP version
- Module version
- Detailed error message
- Steps to reproduce
- Screenshots (if applicable)

## 📄 License

This module is licensed under the **Academic Free License (AFL 3.0)**.

Copyright (c) 2007-2024 PrestaShop SA

This program is free software: you can redistribute it and/or modify it under the terms of the Academic Free License (AFL 3.0) as published by the Free Software Foundation.

For more information, visit: http://opensource.org/licenses/afl-3.0.php

## 👤 Author

**ACC**

## 🙏 Acknowledgments

- PrestaShop SA for the PrestaShop platform
- PrestaShop community for feedback and support

---

**Made with ❤️ for the PrestaShop community**

For more information, visit: [PrestaShop Addons Marketplace](https://addons.prestashop.com/)
