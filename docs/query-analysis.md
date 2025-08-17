# Query Analysis

WP Dumper includes advanced database query analysis tools that help you identify performance issues, duplicate queries, N+1 problems, and optimization opportunities.

## 🎯 Basic Query Analysis

### Starting Query Analysis

```php
// Start logging all database queries
start_query_analysis();

// Your code that executes queries
$posts = get_posts(['post_type' => 'product']);
$users = get_users(['role' => 'customer']);

// Analyze all logged queries
dump_query_analysis();

// Stop logging
stop_query_analysis();
```

### Quick Query Information

```php
// Current query information
dump_query();

// Specific WP_Query analysis
$custom_query = new WP_Query(['post_type' => 'product']);
dump_wp_query($custom_query);

// Quick shortcut
queries(); // Same as dump_query()
```

## 📊 What Gets Analyzed

The query analyzer tracks:

- **Query types** (SELECT, INSERT, UPDATE, DELETE)
- **Tables accessed** and frequency
- **Duplicate queries** (exact matches)
- **Potential slow queries** (based on patterns)
- **N+1 query problems** (repeated similar queries)
- **Query execution context** (backtrace)

## 📈 Analysis Output

### Query Summary
```
summary: {
  total_queries: 47
  unique_queries: 32
  duplicate_queries: 15
  execution_time: "0.234s"
  memory_usage: "12.3 MB"
}
```

### Queries by Type
```
by_type: {
  SELECT: 42
  INSERT: 3
  UPDATE: 2
  DELETE: 0
}
```

### Queries by Table
```
by_table: {
  wp_posts: 15
  wp_postmeta: 12
  wp_users: 8
  wp_usermeta: 6
  wp_options: 4
}
```

### Duplicate Queries
```
duplicate_queries: [
  {
    query: "SELECT * FROM wp_postmeta WHERE post_id = %d"
    count: 8
    first_occurrence: "wp-includes/post.php:123"
    locations: [
      "wp-includes/post.php:123",
      "wp-includes/post.php:456",
      "wp-content/themes/mytheme/functions.php:89"
    ]
  }
]
```

### Slow Query Detection
```
slow_queries: [
  {
    query: "SELECT * FROM wp_posts WHERE post_content LIKE '%search%'"
    score: 7
    issues: [
      "Using SELECT * instead of specific columns",
      "LIKE queries can be slow without proper indexing"
    ]
    backtrace: "wp-content/plugins/myplugin/search.php:45"
  }
]
```

### N+1 Problem Detection
```
n_plus_one: [
  {
    pattern: "SELECT * FROM wp_postmeta WHERE post_id = %d"
    count: 15
    description: "Potential N+1 problem: getting meta for multiple posts individually"
    suggestion: "Consider using get_post_meta() with array of IDs"
  }
]
```

## 🔧 Advanced Analysis

### Analyzing Specific Code Blocks

```php
function analyze_product_loading() {
    start_query_analysis();
    
    // Get products
    $products = get_posts([
        'post_type' => 'product',
        'numberposts' => 10
    ]);
    
    // This might cause N+1 problem
    foreach ($products as $product) {
        $meta = get_post_meta($product->ID);
        $categories = get_the_terms($product->ID, 'product_category');
    }
    
    dump_query_analysis();
    stop_query_analysis();
}
```

### Comparing Before/After Optimization

```php
// Before optimization
start_query_analysis();
$data = get_unoptimized_data();
$before_analysis = dump_query_analysis();
stop_query_analysis();

// After optimization
start_query_analysis();
$data = get_optimized_data();
$after_analysis = dump_query_analysis();
stop_query_analysis();

// Compare results
dump('Before optimization:', $before_analysis);
dump('After optimization:', $after_analysis);
```

## 🎯 Common Query Problems

### 1. N+1 Query Problem

**Problem:**
```php
// This creates N+1 queries (1 + N where N = number of posts)
$posts = get_posts(['numberposts' => 10]);
foreach ($posts as $post) {
    $meta = get_post_meta($post->ID); // 1 query per post
}
```

**Solution:**
```php
// This creates only 2 queries total
$posts = get_posts(['numberposts' => 10]);
$post_ids = wp_list_pluck($posts, 'ID');
$all_meta = get_post_meta($post_ids); // 1 query for all meta
```

### 2. Duplicate Queries

**Problem:**
```php
// These create duplicate queries
$user_1 = get_user_by('id', 123);
$user_2 = get_user_by('id', 123); // Duplicate!
$user_3 = get_user_by('id', 123); // Duplicate!
```

**Solution:**
```php
// Cache the result
static $user_cache = [];
if (!isset($user_cache[123])) {
    $user_cache[123] = get_user_by('id', 123);
}
$user = $user_cache[123];
```

### 3. Inefficient Queries

**Problem:**
```php
// Inefficient: loads all data
$posts = get_posts([
    'numberposts' => -1, // Gets ALL posts
    'meta_query' => [
        [
            'key' => 'featured',
            'value' => 'yes'
        ]
    ]
]);
```

**Solution:**
```php
// Efficient: only get what you need
$posts = get_posts([
    'numberposts' => 10, // Limit results
    'fields' => 'ids', // Only get IDs if that's all you need
    'meta_query' => [
        [
            'key' => 'featured',
            'value' => 'yes'
        ]
    ]
]);
```

## 🔍 Analyzing Specific Scenarios

### WordPress Admin Performance

```php
// Analyze admin dashboard performance
add_action('admin_init', function() {
    if (is_admin() && current_user_can('administrator')) {
        start_query_analysis();
    }
});

add_action('admin_footer', function() {
    if (is_admin() && current_user_can('administrator')) {
        dump_query_analysis();
        stop_query_analysis();
    }
});
```

### Plugin Performance Analysis

```php
// Analyze plugin activation
register_activation_hook(__FILE__, function() {
    start_query_analysis();
    
    // Plugin activation code
    create_plugin_tables();
    setup_default_data();
    
    dump_query_analysis();
    stop_query_analysis();
});
```

### Theme Performance Analysis

```php
// In functions.php
add_action('wp_head', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        start_query_analysis();
    }
}, 1);

add_action('wp_footer', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        dump_query_analysis();
        stop_query_analysis();
    }
}, 999);
```

### Custom Query Optimization

```php
function optimize_product_search($search_term) {
    start_query_analysis();
    
    // Test different query approaches
    $approach_1 = search_products_method_1($search_term);
    dump('Method 1 queries:');
    dump_query_analysis();
    
    stop_query_analysis();
    start_query_analysis();
    
    $approach_2 = search_products_method_2($search_term);
    dump('Method 2 queries:');
    dump_query_analysis();
    
    stop_query_analysis();
}
```

## 📊 Query Performance Scoring

The analyzer assigns performance scores based on:

- **SELECT *** usage (+2 points)
- **ORDER BY without LIMIT** (+3 points)
- **LIKE queries** (+2 points)
- **NOT IN clauses** (+3 points)
- **Multiple JOINs** (+2 points)
- **Very long queries** (+2 points)

Queries with scores ≥4 are flagged as potentially slow.

## 🚀 Optimization Strategies

### Based on Analysis Results

1. **High Duplicate Count**: Implement caching
2. **N+1 Problems**: Batch queries together
3. **Slow Query Scores**: Optimize query structure
4. **High Table Access**: Consider database indexing

### Example Optimizations

```php
// Before: N+1 problem
function get_posts_with_meta_slow() {
    $posts = get_posts(['numberposts' => 20]);
    foreach ($posts as $post) {
        $post->custom_meta = get_post_meta($post->ID, 'custom_field', true);
    }
    return $posts;
}

// After: Optimized
function get_posts_with_meta_fast() {
    $posts = get_posts(['numberposts' => 20]);
    $post_ids = wp_list_pluck($posts, 'ID');
    
    // Get all meta in one query
    global $wpdb;
    $meta_results = $wpdb->get_results($wpdb->prepare("
        SELECT post_id, meta_value 
        FROM {$wpdb->postmeta} 
        WHERE meta_key = 'custom_field' 
        AND post_id IN (" . implode(',', array_fill(0, count($post_ids), '%d')) . ")
    ", ...$post_ids));
    
    // Map meta to posts
    $meta_map = wp_list_pluck($meta_results, 'meta_value', 'post_id');
    foreach ($posts as $post) {
        $post->custom_meta = $meta_map[$post->ID] ?? '';
    }
    
    return $posts;
}
```

## 🔧 Global Query Functions

### Available Functions

```php
// Start/stop analysis
start_query_analysis();
stop_query_analysis();

// Dump analysis results
dump_query_analysis();

// Quick query info
dump_query(); // Current query context
queries(); // Shortcut for dump_query()

// Specific query analysis
dump_wp_query($query_object);
```

## 💡 Best Practices

1. **Analyze in development** before deploying
2. **Focus on high-impact queries** (most frequent/slowest)
3. **Test optimizations** with before/after analysis
4. **Monitor production** with selective analysis
5. **Use caching** for repeated queries
6. **Batch operations** when possible
7. **Limit query results** appropriately

## 🔗 Related Documentation

- [Performance Profiling](profiling.md) - Overall performance analysis
- [WordPress Debugging](wordpress-debugging.md) - WordPress-specific debugging
- [Examples & Recipes](examples.md) - Real-world optimization examples
