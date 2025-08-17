# Basic Usage

Get started with WP Dumper's core functionality for debugging WordPress applications.

## 🚀 Quick Start

### Basic Variable Dumping

```php
// Dump any variable
$user = wp_get_current_user();
dump($user);

// Dump multiple variables
$posts = get_posts(['numberposts' => 5]);
$options = get_option('my_plugin_options');
dump($user, $posts, $options);

// Dump with labels
dump('Current user:', $user);
dump('Recent posts:', $posts);
```

### Dump and Die

```php
// Stop execution and dump
$data = some_complex_function();
dd($data); // This will stop execution here

// This line will never be reached
echo "This won't execute";
```

## 🎯 WordPress-Specific Quick Functions

### WordPress Context
```php
// Quick WordPress state overview
dump_wp();

// WordPress context and die
ddwp();
```

### Performance Monitoring
```php
// Quick performance check
perf(); // Shows execution time, memory, queries

// Environment info
env(); // WordPress version, PHP info, active plugins
```

### Query Information
```php
// Current query info
queries(); // Shows current WP_Query, SQL, query count
```

## 🔧 Profiler Usage

### Basic Profiling

The profiler helps you identify performance bottlenecks by tracking execution time, memory usage, and database queries.

```php
// Start profiling
start_wp_profiling();

// Your code to analyze
do_some_operations();

// Add checkpoints to track progress
wp_checkpoint('After database operations');

more_operations();

wp_checkpoint('After processing');

// Stop and view results
stop_wp_profiling();
```

### What the Profiler Shows

When you call `stop_wp_profiling()`, you'll see:

1. **Execution Summary**
   - Total execution time
   - Memory usage and peak memory
   - Total database queries

2. **Checkpoints**
   - Time between each checkpoint
   - Memory changes
   - Query count changes

3. **Top WordPress Hooks**
   - Most frequently called hooks
   - Helps identify performance bottlenecks

4. **Query Analysis**
   - Query types (SELECT, INSERT, UPDATE, DELETE)
   - Potential slow queries

### Profiler Example Output

```
execution_summary: {
  total_time: "1.2345s"
  memory_used: "12.3 MB"
  peak_memory: "28.7 MB"
  total_queries: 23
}

checkpoints: [
  {
    label: "After database operations"
    elapsed_time: "0.0234s"
    memory_diff: "2.1 MB"
    queries_diff: 8
  },
  {
    label: "After processing"
    elapsed_time: "0.0156s"
    memory_diff: "1.2 MB"
    queries_diff: 3
  }
]

top_hooks: {
  "wp_head": 15
  "wp_enqueue_scripts": 8
  "init": 6
}
```

## 🎯 Common Usage Patterns

### 1. Debug Plugin Performance

```php
// In your plugin's main file
add_action('plugins_loaded', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        start_wp_profiling();
        wp_checkpoint('Plugins loaded');
    }
});

// In your plugin's init function
public function init() {
    wp_checkpoint('Plugin init start');
    
    // Your plugin initialization
    $this->setup_hooks();
    $this->load_dependencies();
    
    wp_checkpoint('Plugin init complete');
}

// At the end of page load
add_action('wp_footer', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        wp_checkpoint('Footer reached');
        stop_wp_profiling();
    }
}, 999);
```

### 2. Debug Theme Performance

```php
// In functions.php
add_action('after_setup_theme', function() {
    if (WP_DEBUG) {
        start_wp_profiling();
        wp_checkpoint('Theme setup');
    }
});

add_action('wp_enqueue_scripts', function() {
    if (WP_DEBUG) {
        wp_checkpoint('Scripts enqueued');
    }
    
    // Your script enqueuing
    wp_enqueue_script('theme-js', get_template_directory_uri() . '/js/main.js');
});

add_action('wp_footer', function() {
    if (WP_DEBUG) {
        wp_checkpoint('Footer complete');
        stop_wp_profiling();
    }
}, 999);
```

### 3. Debug Specific Functions

```php
function my_heavy_function() {
    start_wp_profiling();
    wp_checkpoint('Function start');
    
    // Heavy operation 1
    $data = fetch_external_data();
    wp_checkpoint('External data fetched');
    
    // Heavy operation 2
    $processed = process_data($data);
    wp_checkpoint('Data processed');
    
    // Heavy operation 3
    save_to_database($processed);
    wp_checkpoint('Data saved');
    
    stop_wp_profiling();
    
    return $processed;
}
```

### 4. Debug Page Load Performance

```php
// Track entire page load
add_action('init', function() {
    if (WP_DEBUG && isset($_GET['profile'])) {
        start_wp_profiling();
        wp_checkpoint('WordPress init');
    }
}, 1);

add_action('wp_loaded', function() {
    if (WP_DEBUG && isset($_GET['profile'])) {
        wp_checkpoint('WordPress loaded');
    }
});

add_action('template_redirect', function() {
    if (WP_DEBUG && isset($_GET['profile'])) {
        wp_checkpoint('Template redirect');
    }
});

add_action('wp_footer', function() {
    if (WP_DEBUG && isset($_GET['profile'])) {
        wp_checkpoint('Page complete');
        stop_wp_profiling();
    }
}, 999);

// Usage: Add ?profile=1 to any URL
```

## 🔍 Output Modes

### Browser Display
By default, dumps appear in your browser with styled output.

### Network Display (Dump Server)
When `WP_DEBUG=true`, regular `dump()` calls are sent to a dump server for network viewing:

1. Start the dump server:
   ```bash
   php vendor/bin/var-dump-server
   ```

2. Use `dump()` in your code - output appears in the server

**Note:** `dd()` does NOT send to the network server - it only provides direct output.

### REST API Support
For REST API endpoints, `dd()` returns clean JSON responses instead of HTML.

## 💡 Best Practices

### 1. Conditional Debugging
```php
// Only debug for administrators
if (current_user_can('administrator')) {
    dump_wp();
}

// Only debug in development
if (WP_DEBUG) {
    start_wp_profiling();
}

// Only debug with URL parameter
if (isset($_GET['debug'])) {
    dump($debug_data);
}
```

### 2. Use Descriptive Labels
```php
// Good
wp_checkpoint('After user authentication');
dump('User permissions:', $user_caps);

// Better than
wp_checkpoint('Step 1');
dump($user_caps);
```

### 3. Clean Up Production Code
```php
// Wrap debugging in conditions
if (WP_DEBUG) {
    start_wp_profiling();
    // ... debugging code
    stop_wp_profiling();
}
```

## 🔗 Next Steps

- Learn about [WordPress-specific debugging functions](wordpress-debugging.md)
- Explore [advanced profiling techniques](profiling.md)
- Check out [query analysis and optimization](query-analysis.md)
- See [real-world examples and recipes](examples.md)
