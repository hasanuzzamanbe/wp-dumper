# WordPress-Specific Debugging Guide

WP Dumper includes powerful WordPress-specific debugging functions that provide deep insights into your WordPress application. This guide covers all the advanced debugging features available.

## Table of Contents

1. [Basic Usage](#basic-usage)
2. [WordPress Context Debugging](#wordpress-context-debugging)
3. [User & Authentication](#user--authentication)
4. [Database & Query Analysis](#database--query-analysis)
5. [Performance Profiling](#performance-profiling)
6. [Content & Posts](#content--posts)
7. [Hooks & Actions](#hooks--actions)
8. [Configuration & Options](#configuration--options)
9. [Theme & Templates](#theme--templates)
10. [WordPress Systems](#wordpress-systems)
11. [Quick Shortcuts](#quick-shortcuts)
12. [Advanced Examples](#advanced-examples)

## Basic Usage

### Standard Functions

```php
// Basic variable dumping
dump($variable);

// Dump and die
dd($variable);

// Dump multiple variables
dump($var1, $var2, $var3);
```

### WordPress-Enhanced Functions

```php
// Dump with WordPress context
dump_wp(); // Shows current WP state

// Dump and die with WordPress context
ddwp(); // Shows WP context then dies
ddwp($variable); // Shows WP context, dumps variable, then dies
```

## WordPress Context Debugging

### Current WordPress State

```php
// Get comprehensive WordPress context
dump_wp_context();

// Quick WordPress environment info
dump_wp_env();

// WordPress constants
dump_wp_constants();
dump_wp_constants('DB_'); // Only database constants
dump_wp_constants('WP_DEBUG'); // Constants containing 'WP_DEBUG'
```

**Example Output:**
```php
dump_wp_context();
// Shows: request info, current action/filter, query vars, current post, user, memory usage, etc.
```

## User & Authentication

### User Information

```php
// Current user
dump_user();

// Specific user by ID
dump_user(123);

// User capabilities and roles
dump_caps(); // Current user
dump_caps(123); // Specific user
```

**Example:**
```php
// Debug user permissions for a specific action
if (!current_user_can('edit_posts')) {
    dump_caps(); // See what capabilities the user has
}
```

## Database & Query Analysis

### Basic Query Debugging

```php
// Current query information
dump_query();

// Specific WP_Query
$custom_query = new WP_Query(['post_type' => 'product']);
dump_wp_query($custom_query);

// Quick query info
queries(); // Shortcut for dump_query()
```

### Advanced Query Analysis

```php
// Start comprehensive query logging
start_query_analysis();

// Your code that executes queries
$posts = get_posts(['post_type' => 'product']);
$users = get_users(['role' => 'customer']);

// Analyze all queries (shows duplicates, slow queries, N+1 problems)
dump_query_analysis();

// Stop logging
stop_query_analysis();
```

**Query Analysis Features:**
- **Duplicate Detection**: Finds repeated identical queries
- **N+1 Problem Detection**: Identifies potential N+1 query issues
- **Slow Query Detection**: Flags potentially slow queries
- **Query Type Analysis**: Breaks down SELECT, INSERT, UPDATE, DELETE counts
- **Table Usage**: Shows which tables are queried most

### Example Query Analysis Output

```php
start_query_analysis();
// ... your code ...
dump_query_analysis();

/* Output includes:
{
  "summary": {
    "total_queries": 45,
    "by_type": {"SELECT": 42, "INSERT": 2, "UPDATE": 1},
    "duplicate_count": 12
  },
  "duplicate_queries": [
    {"query": "SELECT * FROM wp_posts WHERE...", "count": 8}
  ],
  "n_plus_one": [
    {"pattern": "SELECT * FROM wp_postmeta WHERE post_id = N", "count": 15}
  ],
  "slow_queries": [
    {"query": "SELECT * FROM wp_posts ORDER BY...", "issues": ["ORDER BY without LIMIT"]}
  ]
}
*/
```

## Performance Profiling

### Comprehensive Profiling

```php
// Start profiling
start_wp_profiling();

// Add checkpoints throughout your code
wp_checkpoint('After plugins loaded');
wp_checkpoint('After theme setup');
wp_checkpoint('Before main query');
wp_checkpoint('After main query');

// Stop and analyze
stop_wp_profiling();
```

### Quick Performance Checks

```php
// Quick performance snapshot
dump_performance();
perf(); // Shortcut

// Memory and execution time
dump([
    'memory' => size_format(memory_get_usage(true)),
    'time' => timer_stop(0, 3) . 's',
    'queries' => get_num_queries()
]);
```

## Content & Posts

### Post Debugging

```php
// Current post with all metadata
dump_post();

// Specific post by ID
dump_post(123);

// Post with custom fields and terms
$post_id = 123;
dump([
    'post' => get_post($post_id),
    'meta' => get_post_meta($post_id),
    'terms' => wp_get_post_terms($post_id, get_object_taxonomies(get_post_type($post_id)))
]);
```

### Template Debugging

```php
// Current template and hierarchy
dump_template();

/* Shows:
- Current template file
- Template hierarchy for current page type
- All WordPress conditional functions (is_home, is_single, etc.)
- Queried object
*/
```

## Hooks & Actions

### Hook Analysis

```php
// All registered hooks
dump_hooks();

// Specific hook
dump_hooks('wp_head');
dump_hooks('init');

// During profiling, see most called hooks
start_wp_profiling();
// ... your code ...
stop_wp_profiling(); // Shows top hooks by call count
```

### Custom Hook Debugging

```php
// Debug what's hooked to a specific action
add_action('wp_head', function() {
    dump_hooks('wp_head');
}, 999);
```

## Configuration & Options

### WordPress Options

```php
// All options (careful - this is huge!)
dump_options();

// Filtered options
dump_options('theme'); // Options containing 'theme'
dump_options('_transient'); // All transients
```

### Transients

```php
// All transients
dump_transients();

// Specific transients
dump_transients('cache'); // Transients containing 'cache'

// Check specific transient
$transient_value = get_transient('my_cache_key');
dump([
    'value' => $transient_value,
    'exists' => $transient_value !== false
]);
```

## Theme & Templates

### Menu Debugging

```php
// All menus
dump_menus();

// Specific menu location
dump_menus('primary');
dump_menus('footer');
```

### Widget Debugging

```php
// All widgets and sidebars
dump_widgets();

// Specific sidebar
dump_widgets('sidebar-1');
```

## WordPress Systems

### Cron Jobs

```php
// All scheduled cron jobs
dump_cron();

/* Shows:
- All scheduled events
- Next run times
- Available schedules
- Whether cron is disabled
*/
```

### Rewrite Rules

```php
// All rewrite rules
dump_rewrites();

// Specific rules
dump_rewrites('product'); // Rules containing 'product'
dump_rewrites('api'); // API-related rules
```

### REST API

```php
// REST API information
dump_rest();

/* Shows:
- Whether REST is enabled
- Available routes
- Current user REST capabilities
*/
```

## Quick Shortcuts

```php
// Performance
perf(); // Same as dump_performance()

// Environment
env(); // Same as dump_wp_env()

// Queries
queries(); // Same as dump_query()

// WordPress state
dump_wp(); // Same as dump_wp_context()
```

## Advanced Examples

### Debugging Plugin Conflicts

```php
// Check what's hooked to problematic actions
dump_hooks('wp_head');
dump_hooks('wp_footer');
dump_hooks('wp_enqueue_scripts');

// See active plugins
dump_wp_env(); // Includes active plugins list
```

### Debugging Slow Pages

```php
start_wp_profiling();
start_query_analysis();

wp_checkpoint('Start of problematic function');
// ... your slow code ...
wp_checkpoint('End of problematic function');

stop_wp_profiling(); // Shows execution time and memory
dump_query_analysis(); // Shows query issues
```

### Debugging Custom Post Types

```php
// Check post type registration
global $wp_post_types;
dump($wp_post_types['your_post_type']);

// Check rewrite rules for CPT
dump_rewrites('your_post_type');

// Check template hierarchy
dump_template(); // When viewing CPT archive/single
```

### Debugging User Permissions

```php
// Check specific capability
$user_id = 123;
$capability = 'edit_private_posts';

dump([
    'user_can' => user_can($user_id, $capability),
    'current_user_can' => current_user_can($capability),
    'user_caps' => get_user_by('id', $user_id)->allcaps,
]);

dump_caps($user_id); // Full capability analysis
```

### Debugging AJAX Requests

```php
// In your AJAX handler
add_action('wp_ajax_my_action', function() {
    dump_wp_context(); // Shows AJAX context
    
    // Your AJAX code
    $result = do_something();
    
    dump($result);
    wp_die();
});
```

### Debugging Query Performance

```php
// Before a complex query
start_query_analysis();

$complex_query = new WP_Query([
    'post_type' => 'product',
    'meta_query' => [
        [
            'key' => 'featured',
            'value' => 'yes'
        ]
    ],
    'tax_query' => [
        [
            'taxonomy' => 'product_category',
            'field' => 'slug',
            'terms' => 'electronics'
        ]
    ]
]);

dump_query_analysis(); // Shows if query is optimized
dump_wp_query($complex_query); // Shows query details
```

## Tips for Effective WordPress Debugging

1. **Use MU-Plugin Loading**: Load WP Dumper as a must-use plugin to debug other plugins' initialization
2. **Combine Functions**: Use multiple debugging functions together for comprehensive analysis
3. **Use Checkpoints**: Add performance checkpoints throughout complex operations
4. **Profile Queries**: Always analyze queries when debugging performance issues
5. **Check Hooks**: When debugging unexpected behavior, check what's hooked to relevant actions
6. **Use Shortcuts**: The short functions (`perf()`, `env()`, `queries()`) are great for quick debugging

This comprehensive debugging toolkit makes WordPress development much more efficient by providing deep insights into your application's behavior, performance, and structure.
