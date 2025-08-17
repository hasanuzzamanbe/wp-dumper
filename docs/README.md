# WP Dumper Documentation

A comprehensive WordPress debugging plugin that provides Laravel-style variable dumping with advanced WordPress-specific debugging capabilities.

## 📚 Documentation Index

### Getting Started
- [Installation & Setup](installation.md)
- [Basic Usage](basic-usage.md)
- [Configuration](configuration.md)

### Core Features
- [Basic Dumping Functions](basic-dumping.md) - `dump()`, `dd()`, and core functionality
- [WordPress Debugging](wordpress-debugging.md) - WordPress-specific debugging functions
- [Performance Profiling](profiling.md) - Advanced performance analysis and profiling
- [Query Analysis](query-analysis.md) - Database query debugging and optimization
- [REST API Debugging](rest-api-debugging.md) - Debugging REST API and AJAX requests

### Advanced Features
- [Template Debugging](template-debugging.md) - Theme and template debugging
- [Hook & Filter Analysis](hooks-debugging.md) - WordPress hooks and actions debugging
- [User & Capabilities](user-debugging.md) - User roles and capabilities debugging
- [Environment Analysis](environment-debugging.md) - WordPress environment and configuration

### Reference
- [Function Reference](function-reference.md) - Complete list of all available functions
- [Examples & Recipes](examples.md) - Common debugging scenarios and solutions
- [Troubleshooting](troubleshooting.md) - Common issues and solutions

## 🚀 Quick Start

### Basic Dumping
```php
// Basic variable dumping
dump($variable);

// Dump and die
dd($variable);

// WordPress context
dump_wp(); // Current WordPress state
ddwp(); // Dump WordPress context and die
```

### Performance Profiling
```php
// Start profiling
start_wp_profiling();

// Add checkpoints
wp_checkpoint('After plugin init');
wp_checkpoint('After theme setup');

// Stop and view results
stop_wp_profiling();
```

### Query Analysis
```php
// Start query analysis
start_query_analysis();

// Your code that runs queries
$posts = get_posts(['post_type' => 'product']);

// Analyze queries (shows duplicates, slow queries, N+1 problems)
dump_query_analysis();
```

### WordPress-Specific Debugging
```php
// User debugging
dump_user(); // Current user
dump_caps(); // User capabilities

// Query debugging
dump_query(); // Current WP_Query
dump_wp_query($custom_query); // Specific query

// Template debugging
dump_template(); // Current template info

// Hook debugging
dump_hooks('wp_head'); // Specific hook
dump_hooks(); // All hooks
```

## 🎯 Key Features

- **Laravel-style dumping** with beautiful HTML output
- **Dual output modes**: Browser display or dedicated dump server
- **WordPress-specific functions** for debugging WP core functionality
- **Performance profiling** with checkpoints and timing
- **Advanced query analysis** with duplicate detection and N+1 problem identification
- **REST API support** with clean JSON responses
- **Template hierarchy debugging** for theme development
- **Hook and filter analysis** for plugin development
- **User capability debugging** for permission issues
- **Environment analysis** for configuration debugging

## 🔧 Output Modes

### Browser Display
Variables are displayed directly in your browser with styled output.

### Dump Server (Network Display)
When `WP_DEBUG=true`, dumps are sent to a local server (localhost:9913) for network viewing.

### REST API Support
Clean JSON responses for REST API endpoints with formatted dump data.

## 📖 Documentation Structure

Each documentation file covers specific aspects of the plugin:

- **Installation & Setup**: Getting the plugin running
- **Basic Usage**: Core dumping functions and concepts
- **WordPress Debugging**: WordPress-specific debugging functions
- **Profiling**: Performance analysis and optimization
- **Query Analysis**: Database query debugging
- **Examples**: Real-world debugging scenarios

## 🆘 Need Help?

- Check the [Troubleshooting Guide](troubleshooting.md) for common issues
- Review [Examples & Recipes](examples.md) for specific use cases
- See the [Function Reference](function-reference.md) for complete API documentation

## 🔗 Quick Links

- [Basic Dumping Functions →](basic-dumping.md)
- [WordPress Debugging →](wordpress-debugging.md)
- [Performance Profiling →](profiling.md)
- [Query Analysis →](query-analysis.md)
- [Function Reference →](function-reference.md)
