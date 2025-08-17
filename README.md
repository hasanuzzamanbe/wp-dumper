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
[Debug Log Watcher
]()

## Basic Usage

### Standard Functions

```php
// Dump variables without stopping execution
dump($variable);
dump($var1, $var2, $var3);

// Dump and die (stops execution)
dd($variable);
```

### WordPress-Specific Functions

WP Dumper includes powerful WordPress-specific debugging functions:

```php
// Quick WordPress context
dump_wp(); // Current WordPress state
ddwp(); // Dump WordPress context and die

// User debugging
dump_user(); // Current user info
dump_caps(); // User capabilities

// Query debugging
dump_query(); // Current query info
dump_wp_query($custom_query); // Specific WP_Query

// Performance profiling
start_wp_profiling();
wp_checkpoint('After plugin init');
stop_wp_profiling();

// Quick shortcuts
perf(); // Performance metrics
env(); // Environment info
queries(); // Query information
```

## 📖 Complete Documentation

For comprehensive documentation, see the **[docs/](docs/)** directory:

### 📚 Documentation Index
- **[Installation & Setup](docs/installation.md)** - Get started with WP Dumper
- **[WordPress Debugging](docs/wordpress-debugging.md)** - WordPress-specific debugging
- **[Performance Profiling](docs/profiling.md)** - Advanced performance analysis
- **[Query Analysis](docs/query-analysis.md)** - Database query debugging
- **[REST API Debugging](docs/rest-api-debugging.md)** - REST API and AJAX debugging
- **[Function Reference](docs/function-reference.md)** - Complete function list
- **[Examples & Recipes](docs/examples.md)** - Real-world debugging scenarios

### 🚀 Quick Links
- [How to use the profiler →](docs/profiling.md)
- [WordPress debugging functions →](docs/wordpress-debugging.md)
- [Query optimization examples →](docs/query-analysis.md)
- [Complete function reference →](docs/function-reference.md)

### Advanced Dumping with Server Mode

For Laravel Herd-like experience:

1. Install debug-log-watcher: [Debug Log Watcher](https://drive.google.com/drive/folders/1xMLx-Fmnf5P1YendcKRmPzzbiVyZvqeT?usp=sharing)
2. Enable `WP_DEBUG` in `wp-config.php`
3. Use `dump()` - output appears in the debug-log-watcher app

## Configuration

### WordPress Debug Mode

```php
// Add to wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## Examples

### Debug Plugin Performance
```php
start_wp_profiling();
start_query_analysis();

// Your plugin code here

stop_wp_profiling(); // Shows execution time, memory, hook calls
dump_query_analysis(); // Shows query optimization opportunities
```

### Debug User Permissions
```php
if (!current_user_can('edit_posts')) {
    dump_caps(); // See what capabilities user has
    dump_user(); // Full user information
}
```

### Debug Template Issues
```php
dump_template(); // Shows current template, hierarchy, conditionals
dump_wp_context(); // Full WordPress state
```

## Available WordPress Debug Functions

| Function | Description |
|----------|-------------|
| `dump_wp()` | Current WordPress state and context |
| `dump_user($id)` | User information and metadata |
| `dump_caps($id)` | User capabilities and roles |
| `dump_query()` | Current query information |
| `dump_wp_query($query)` | Specific WP_Query analysis |
| `dump_post($id)` | Post with metadata and terms |
| `dump_template()` | Template hierarchy and conditionals |
| `dump_hooks($hook)` | Registered hooks and callbacks |
| `dump_options($pattern)` | WordPress options |
| `dump_transients($pattern)` | Cached transients |
| `dump_cron()` | Scheduled cron jobs |
| `dump_menus($location)` | Navigation menus |
| `dump_widgets($sidebar)` | Widgets and sidebars |
| `dump_rewrites($pattern)` | Rewrite rules |
| `dump_rest()` | REST API information |
| `dump_wp_env()` | WordPress environment |
| `dump_wp_constants($pattern)` | WordPress constants |
| `dump_performance()` | Performance metrics |

## Profiling Functions

| Function | Description |
|----------|-------------|
| `start_wp_profiling()` | Begin comprehensive profiling |
| `stop_wp_profiling()` | End profiling and show results |
| `wp_checkpoint($label)` | Add performance checkpoint |
| `start_query_analysis()` | Begin query logging |
| `dump_query_analysis()` | Analyze logged queries |
| `stop_query_analysis()` | Stop query logging |

## Quick Shortcuts

| Shortcut | Equivalent |
|----------|------------|
| `perf()` | `dump_performance()` |
| `env()` | `dump_wp_env()` |
| `queries()` | `dump_query()` |

## Troubleshooting

### "Headers already sent" Error
This plugin uses output buffering to prevent this error. If you use `dd()` very early in WordPress loading, use `dump()` instead.

### Composer Dependencies Missing
If you see an admin notice about missing Composer autoloader:
1. Navigate to the plugin directory
2. Run `composer install`
3. Refresh your WordPress admin

### Functions Not Available
Make sure the plugin is activated. If using as MU-plugin, ensure all helper files are included.

## License

This plugin is licensed under the GPL v2 or later.

## Author

**Hasanuzzaman**
- Website: [hasanuzzaman.com](https://hasanuzzaman.com/)
- Plugin URI: [wpminers.com](https://wpminers.com/)

## Changelog

### Version 2.2.0
- Added comprehensive WordPress-specific debugging functions
- Added performance profiling with checkpoints
- Added advanced query analysis (N+1 detection, slow query detection)
- Added user and capability debugging
- Added template and hook debugging
- Added WordPress environment analysis
- Added quick shortcut functions
- Improved output buffering
- Enhanced server mode functionality

---

For complete documentation and advanced examples, see [WORDPRESS-DEBUGGING.md](WORDPRESS-DEBUGGING.md)

For more information and support, please visit the [plugin homepage](https://wpminers.com/).

