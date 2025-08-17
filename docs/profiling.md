# Performance Profiling

WP Dumper includes a powerful profiling system that helps you identify performance bottlenecks in your WordPress application.

## 📊 Basic Profiling

### Starting and Stopping Profiling

```php
// Start profiling
start_wp_profiling();

// Your code to profile
do_some_heavy_operations();

// Stop profiling and view results
stop_wp_profiling();
```

### Adding Checkpoints

Checkpoints help you track performance at specific points in your code:

```php
start_wp_profiling();

wp_checkpoint('Plugin initialization');
// Plugin init code...

wp_checkpoint('Database queries');
// Database operations...

wp_checkpoint('Template rendering');
// Template code...

stop_wp_profiling();
```

## 🎯 What Gets Profiled

The profiler automatically tracks:

- **Execution time** between checkpoints
- **Memory usage** and memory differences
- **Database queries** executed
- **WordPress hooks** called (with call counts)
- **Overall performance metrics**

## 📈 Profiling Output

When you call `stop_wp_profiling()`, you'll see:

### Execution Summary
```
execution_summary: {
  total_time: "2.1234s"
  memory_used: "15.2 MB"
  peak_memory: "32.1 MB"
  total_queries: 47
}
```

### Checkpoints
```
checkpoints: [
  {
    label: "Plugin initialization"
    elapsed_time: "0.0123s"
    memory_diff: "2.1 MB"
    queries_diff: 5
    total_time: "0.0123s"
    total_memory: "12.3 MB"
  },
  {
    label: "Database queries"
    elapsed_time: "0.0456s"
    memory_diff: "1.8 MB"
    queries_diff: 12
    total_time: "0.0579s"
    total_memory: "14.1 MB"
  }
]
```

### Top Hooks
```
top_hooks: {
  "wp_head": 23
  "wp_enqueue_scripts": 15
  "init": 12
  "wp_footer": 8
}
```

### Query Analysis
```
query_summary: {
  total_queries: 47
  select_queries: 42
  insert_queries: 3
  update_queries: 2
  delete_queries: 0
}

slow_queries: [
  {
    query: "SELECT * FROM wp_posts WHERE..."
    backtrace: "wp-includes/post.php:123"
  }
]
```

## 🔧 Advanced Profiling

### Profiling Specific Code Blocks

```php
// Profile a specific function
function my_heavy_function() {
    start_wp_profiling();
    
    wp_checkpoint('Start heavy operation');
    
    // Heavy code here
    for ($i = 0; $i < 1000; $i++) {
        // Some operation
    }
    
    wp_checkpoint('End heavy operation');
    
    stop_wp_profiling();
}
```

### Profiling WordPress Hooks

```php
// Profile what happens during wp_head
add_action('wp_head', function() {
    start_wp_profiling();
    wp_checkpoint('wp_head start');
}, 1);

add_action('wp_head', function() {
    wp_checkpoint('wp_head end');
    stop_wp_profiling();
}, 999);
```

### Profiling Page Load

```php
// In your theme's functions.php
add_action('init', function() {
    start_wp_profiling();
    wp_checkpoint('WordPress init');
});

add_action('wp_footer', function() {
    wp_checkpoint('Before footer');
    stop_wp_profiling();
}, 999);
```

## 📊 Performance Metrics

### Quick Performance Check

```php
// Quick performance snapshot
dump_performance();

/* Shows:
- Execution time
- Memory usage (current, peak, limit)
- Database queries
- Loaded plugins count
*/
```

### Using the `perf()` Shortcut

```php
// Same as dump_performance()
perf();
```

## 🎯 Common Profiling Scenarios

### 1. Plugin Performance Analysis

```php
// Profile plugin activation
register_activation_hook(__FILE__, function() {
    start_wp_profiling();
    wp_checkpoint('Plugin activation start');
    
    // Plugin activation code
    create_database_tables();
    wp_checkpoint('Database tables created');
    
    setup_default_options();
    wp_checkpoint('Default options set');
    
    stop_wp_profiling();
});
```

### 2. Theme Performance Analysis

```php
// In functions.php
add_action('after_setup_theme', function() {
    start_wp_profiling();
    wp_checkpoint('Theme setup start');
});

add_action('wp_enqueue_scripts', function() {
    wp_checkpoint('Scripts enqueued');
});

add_action('wp_footer', function() {
    wp_checkpoint('Footer rendered');
    stop_wp_profiling();
}, 999);
```

### 3. Custom Query Performance

```php
function get_featured_products() {
    start_wp_profiling();
    wp_checkpoint('Query start');
    
    $products = new WP_Query([
        'post_type' => 'product',
        'meta_query' => [
            [
                'key' => 'featured',
                'value' => 'yes'
            ]
        ]
    ]);
    
    wp_checkpoint('Query executed');
    
    $processed = process_products($products->posts);
    wp_checkpoint('Products processed');
    
    stop_wp_profiling();
    
    return $processed;
}
```

### 4. AJAX Performance Analysis

```php
add_action('wp_ajax_my_action', function() {
    start_wp_profiling();
    wp_checkpoint('AJAX start');
    
    // AJAX processing
    $data = process_ajax_request();
    wp_checkpoint('Request processed');
    
    wp_send_json_success($data);
    wp_checkpoint('Response sent');
    
    stop_wp_profiling();
});
```

## 🚀 Performance Optimization Tips

### Based on Profiling Results

1. **High Memory Usage**: Look for memory leaks in loops or large data structures
2. **Many Database Queries**: Consider caching or query optimization
3. **Slow Checkpoints**: Identify bottleneck functions
4. **High Hook Counts**: Review unnecessary hook callbacks

### Example Optimization

```php
// Before optimization
function get_user_posts($user_id) {
    start_wp_profiling();
    
    $posts = get_posts(['author' => $user_id]);
    wp_checkpoint('Posts retrieved');
    
    foreach ($posts as $post) {
        $meta = get_post_meta($post->ID); // N+1 problem!
        wp_checkpoint("Meta for post {$post->ID}");
    }
    
    stop_wp_profiling();
}

// After optimization
function get_user_posts_optimized($user_id) {
    start_wp_profiling();
    
    $posts = get_posts(['author' => $user_id]);
    wp_checkpoint('Posts retrieved');
    
    // Get all meta in one query
    $post_ids = wp_list_pluck($posts, 'ID');
    $all_meta = get_post_meta($post_ids);
    wp_checkpoint('All meta retrieved');
    
    stop_wp_profiling();
}
```

## 🔍 Analyzing Results

### Reading Checkpoint Data

- **elapsed_time**: Time since last checkpoint
- **memory_diff**: Memory change since last checkpoint
- **queries_diff**: Queries executed since last checkpoint
- **total_time**: Total time since profiling started
- **total_memory**: Total memory usage at this point

### Identifying Bottlenecks

1. Look for checkpoints with high `elapsed_time`
2. Check for large `memory_diff` values
3. Watch for high `queries_diff` numbers
4. Review the `top_hooks` for unexpected high counts

## 💡 Best Practices

1. **Use descriptive checkpoint labels**
2. **Profile in development environment first**
3. **Remove profiling code from production**
4. **Focus on the biggest bottlenecks first**
5. **Profile before and after optimizations**
6. **Use profiling with query analysis for complete picture**

## 🔗 Related Documentation

- [Query Analysis](query-analysis.md) - Database query debugging
- [WordPress Debugging](wordpress-debugging.md) - WordPress-specific debugging
