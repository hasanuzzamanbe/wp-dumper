# WP Dumper

A WordPress plugin that provides Laravel-style variable dumping functionality using Symfony's VarDumper component. This plugin allows you to debug variables in a clean, readable format either in your browser or through a dedicated dump server.

## Features

- 🔍 **Laravel-style `dump()` and `dd()` functions** for WordPress
- 🎨 **Beautiful HTML output** powered by Symfony VarDumper
- 🖥️ **Dual output modes**: Browser footer or dedicated dump server
- ⚡ **Non-blocking server requests** to maintain performance
- 🛡️ **Safe output buffering** to prevent "headers already sent" errors
- 🎯 **Source tracking** shows file and line number where dump was called

## Installation

### Method 1: Manual Installation

1. Download or clone this repository into your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   composer install
   ```
   Then activate the plugin. and use `dump()` and `dd()` functions.

## Requirements

- **PHP**: 7.4 or higher
- **WordPress**: 5.0 or higher
- **Composer**: For dependency management

## Usage

### Basic Dumping

Use the `dd()` function to output variables into browser directly.

### Advanced Dumping `dump()` like laravel herd without any interruption for free!

Automatic setup:
Install this tools debug-log-watcher on your Mac
[https://drive.google.com/drive/folders/1xMLx-Fmnf5P1YendcKRmPzzbiVyZvqeT?usp=sharing
](Debug Log Watcher)

## Usage:

1. Install the debug-log-watcher on your Mac.
2. Open the debug-log-watcher app.
3. Click on the "Start" button.
4. Open your browser and navigate to your WordPress site.
5. Use the `dump()` function to output variables.
6. The output will be displayed in the debug-log-watcher app.

