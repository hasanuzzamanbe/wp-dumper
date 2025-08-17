# WordPress Debugging

WP Dumper provides specialized debugging functions tailored for WordPress development, making it easy to inspect WordPress-specific objects, queries, users, and system state.

## 🎯 WordPress Context

### `dump_wp()` - WordPress State
Get a complete overview of the current WordPress context.

```php
dump_wp();
```

**Shows:**
- Request information (method, URI, user agent)
- WordPress state (current action/filter, AJAX, REST, cron status)
- Query variables from `$wp_query`
- Current post information
- Current user ID
- Memory usage and execution time

### `ddwp()` - WordPress Context and Die
Dump WordPress context and optionally additional variables, then stop execution.

```php
ddwp(); // Just WordPress context
ddwp($additional_data); // WordPress context + your data
```

## 🔍 Query Debugging

### `dump_query()` - Current Query Info
Analyze the current WordPress query and performance.

```php
dump_query();
```

**Shows:**
- Main query object (`$wp_query`)
- Last executed SQL query
- Total query count
- Execution time
- Memory usage

### `dump_wp_query()` - Detailed Query Analysis
Deep dive into any WP_Query object.

```php
$custom_query = new WP_Query(['post_type' => 'product']);
dump_wp_query($custom_query);

// Or analyze the main query
dump_wp_query();
```

**Shows:**
- Query variables
- Generated SQL request
- Found posts and pagination info
- WordPress conditional functions (is_home, is_single, etc.)
- Queried object

### Quick Query Shortcut
```php
queries(); // Same as dump_query()
```

## 👤 User & Capabilities Debugging

### `dump_user()` - User Information
Analyze user data and capabilities.

```php
dump_user(); // Current user
dump_user(123); // Specific user by ID
```

**Shows:**
- Basic user info (ID, login, email, display name)
- User roles
- All user capabilities
- User meta data

### `dump_caps()` - Capability Analysis
Detailed breakdown of user capabilities.

```php
dump_caps(); // Current user capabilities
dump_caps(123); // Specific user capabilities
```

**Shows:**
- User information
- All effective capabilities
- Capabilities by role
- Role inheritance

### Example: Debugging Permission Issues
```php
// Check if user can perform action
if (!current_user_can('edit_posts')) {
    dump_caps(); // See what capabilities user actually has
    dd('User cannot edit posts');
}
```

## 🎨 Template & Theme Debugging

### `dump_template()` - Template Information
Understand which template is being used and why.

```php
dump_template();
```

**Shows:**
- Current template file path
- Template hierarchy for current page type
- All WordPress conditional functions (is_home, is_single, etc.)
- Queried object (post, term, user, etc.)

### Example: Template Debugging
```php
// In your theme files
add_action('wp_head', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        dump_template();
    }
});
```

## 🔗 Hooks & Actions Debugging

### `dump_hooks()` - Hook Analysis
See what's attached to WordPress hooks.

```php
dump_hooks(); // All registered hooks
dump_hooks('wp_head'); // Specific hook
dump_hooks('init'); // Another specific hook
```

**Shows:**
- Hook callbacks organized by priority
- Function names and accepted arguments
- Which plugins/themes added the callbacks

### Example: Debugging Plugin Conflicts
```php
// See what's hooked to problematic actions
dump_hooks('wp_enqueue_scripts');
dump_hooks('wp_head');
dump_hooks('wp_footer');
```

## 🌍 Environment & Configuration

### `dump_wp_env()` - Environment Analysis
Complete WordPress environment overview.

```php
dump_wp_env();
```

**Shows:**
- WordPress version and configuration
- PHP version and important settings
- Server information
- Active plugins and theme
- Important WordPress constants
- Database information

### Quick Environment Shortcut
```php
env(); // Same as dump_wp_env()
```

### `dump_wp_constants()` - WordPress Constants
Inspect WordPress configuration constants.

```php
dump_wp_constants(); // All WordPress constants
dump_wp_constants('DB_'); // Database constants only
dump_wp_constants('WP_DEBUG'); // Debug-related constants
```

## 📊 Performance Debugging

### `dump_performance()` - Performance Metrics
Quick performance snapshot.

```php
dump_performance();
```

**Shows:**
- Execution time since page load
- Memory usage (current, peak, limit)
- Database query count and time
- Number of loaded plugins

### Quick Performance Shortcut
```php
perf(); // Same as dump_performance()
```

## 🔌 Widget Debugging

### `dump_widgets()` - Widget Analysis
Debug WordPress widgets and sidebars.

```php
dump_widgets(); // All widgets and sidebars
dump_widgets('sidebar-1'); // Specific sidebar
```

**Shows:**
- Registered sidebars
- Active widgets in each sidebar
- Widget configuration and settings

## 🌐 REST API Debugging

### `dump_rest()` - REST API Status
Check REST API configuration and availability.

```php
dump_rest();
```

**Shows:**
- Whether REST API is enabled
- Available routes (sample)
- Current user REST capabilities
- REST API URL

### `dump_rest_request()` - REST Request Info
Debug current REST API request (only works within REST requests).

```php
// In a REST API endpoint
add_action('rest_api_init', function() {
    register_rest_route('myplugin/v1', '/debug', [
        'methods' => 'POST',
        'callback' => function($request) {
            dump_rest_request();
            return ['status' => 'success'];
        }
    ]);
});
```

**Shows:**
- Request method and route
- Request parameters and body
- Headers and authentication status
- Nonce validation status

### `dump_rest_route()` - REST Route Info
Analyze REST API routes and handlers.

```php
dump_rest_route();
```

**Shows:**
- Current request URI
- Matching routes and their handlers
- Total available routes

## 🎯 Common WordPress Debugging Scenarios

### 1. Page Not Loading Correctly
```php
// Add to your theme's functions.php or problematic template
dump_wp(); // See current WordPress state
dump_template(); // Check template hierarchy
dump_query(); // Analyze the query
```

### 2. User Permission Issues
```php
// When user can't access something
dump_user(); // See user info
dump_caps(); // Check capabilities
dump(['can_edit_posts' => current_user_can('edit_posts')]);
```

### 3. Plugin Conflicts
```php
// Check what's hooked to problematic actions
dump_hooks('wp_head');
dump_hooks('wp_enqueue_scripts');
dump_wp_env(); // See active plugins
```

### 4. Custom Query Not Working
```php
$args = [
    'post_type' => 'product',
    'meta_query' => [
        ['key' => 'featured', 'value' => 'yes']
    ]
];

dump('Query args:', $args);

$query = new WP_Query($args);
dump_wp_query($query);

if (!$query->have_posts()) {
    dd('No posts found with these args');
}
```

### 5. AJAX Request Issues
```php
add_action('wp_ajax_my_action', function() {
    dump_wp(); // WordPress context
    dump('POST data:', $_POST);
    
    // Your AJAX processing
    $result = process_ajax_data($_POST);
    
    dump('Result:', $result);
    wp_send_json_success($result);
});
```

### 6. REST API Endpoint Issues
```php
register_rest_route('myplugin/v1', '/test', [
    'methods' => 'POST',
    'callback' => function($request) {
        dump_rest_request(); // Request details
        
        $data = $request->get_json_params();
        dump('Request data:', $data);
        
        return ['message' => 'Success'];
    }
]);
```

### 7. Theme Development
```php
// In your theme files
add_action('wp_head', function() {
    if (WP_DEBUG && is_user_logged_in()) {
        dump_template(); // Template info
        dump_wp(); // WordPress state
    }
}, 999);
```

### 8. Performance Issues
```php
// Check what's consuming resources
perf(); // Quick performance check
dump_hooks('wp_head'); // See what's in wp_head
dump_wp_env(); // Check active plugins
```

## 💡 Best Practices

### 1. Conditional Debugging
```php
// Only debug for administrators
if (current_user_can('administrator')) {
    dump_wp();
}

// Only debug in development
if (WP_DEBUG) {
    dump_query();
}

// Only debug on specific pages
if (is_single() && get_post_type() === 'product') {
    dump_template();
}
```

### 2. Organized Debugging
```php
// Group related debugging
dump('=== WordPress State ===');
dump_wp();

dump('=== Query Information ===');
dump_query();

dump('=== User Information ===');
dump_user();
```

### 3. Debugging Hooks Safely
```php
// Debug hooks without affecting functionality
add_action('wp_head', function() {
    dump_hooks('wp_head');
}, 999); // Run last
```

## 🔗 Related Documentation

- [Basic Dumping](basic-dumping.md) - Core dump functions
- [Performance Profiling](profiling.md) - Performance analysis
- [Query Analysis](query-analysis.md) - Database debugging
- [Function Reference](function-reference.md) - Complete function list
