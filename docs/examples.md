# Examples & Recipes

Real-world debugging scenarios and solutions using WP Dumper.

## 🎯 Common Debugging Scenarios

### 1. Plugin Development

#### Debugging Plugin Activation
```php
register_activation_hook(__FILE__, function() {
    start_wp_profiling();
    wp_checkpoint('Activation started');
    
    // Create database tables
    create_plugin_tables();
    wp_checkpoint('Tables created');
    
    // Set default options
    add_option('myplugin_version', '1.0.0');
    wp_checkpoint('Options set');
    
    stop_wp_profiling();
});
```

#### Debugging Custom Post Types
```php
add_action('init', function() {
    // Debug before registration
    dump('Registering custom post type...');
    
    register_post_type('product', [
        'public' => true,
        'supports' => ['title', 'editor', 'thumbnail']
    ]);
    
    // Verify registration
    dump('Post type registered:', post_type_exists('product'));
    dump('Post type object:', get_post_type_object('product'));
});
```

#### Debugging Meta Boxes
```php
add_action('add_meta_boxes', function() {
    dump('Adding meta boxes for post type:', get_post_type());
    
    add_meta_box(
        'product_details',
        'Product Details',
        'product_meta_box_callback',
        'product'
    );
    
    // Debug registered meta boxes
    global $wp_meta_boxes;
    dump('Registered meta boxes:', $wp_meta_boxes);
});
```

### 2. Theme Development

#### Debugging Template Hierarchy
```php
// In functions.php
add_action('template_redirect', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        dump_template();
        
        // Show template hierarchy for current request
        dump([
            'is_home' => is_home(),
            'is_front_page' => is_front_page(),
            'is_single' => is_single(),
            'is_page' => is_page(),
            'is_archive' => is_archive(),
            'post_type' => get_post_type(),
            'template_hierarchy' => get_template_hierarchy()
        ]);
    }
});

function get_template_hierarchy() {
    $hierarchy = [];
    
    if (is_single()) {
        $post = get_queried_object();
        $hierarchy = [
            "single-{$post->post_type}-{$post->post_name}.php",
            "single-{$post->post_type}.php",
            "single.php",
            "singular.php",
            "index.php"
        ];
    } elseif (is_page()) {
        $page = get_queried_object();
        $hierarchy = [
            "page-{$page->post_name}.php",
            "page-{$page->ID}.php",
            "page.php",
            "singular.php",
            "index.php"
        ];
    }
    
    return $hierarchy;
}
```

#### Debugging Enqueued Scripts and Styles
```php
add_action('wp_enqueue_scripts', function() {
    // Your enqueue code
    wp_enqueue_style('theme-style', get_stylesheet_uri());
    wp_enqueue_script('theme-script', get_template_directory_uri() . '/js/main.js');
    
    // Debug what's enqueued
    if (WP_DEBUG) {
        global $wp_styles, $wp_scripts;
        dump('Enqueued styles:', array_keys($wp_styles->registered));
        dump('Enqueued scripts:', array_keys($wp_scripts->registered));
    }
});
```

### 3. Query Optimization

#### Finding N+1 Query Problems
```php
function debug_post_loop() {
    start_query_analysis();
    
    $posts = get_posts(['numberposts' => 10]);
    
    foreach ($posts as $post) {
        // This creates N+1 problem
        $meta = get_post_meta($post->ID, 'custom_field', true);
        $author = get_user_by('id', $post->post_author);
        
        dump("Post {$post->ID}: {$meta}");
    }
    
    dump_query_analysis(); // Will show duplicate queries
    stop_query_analysis();
}

function optimized_post_loop() {
    start_query_analysis();
    
    $posts = get_posts(['numberposts' => 10]);
    $post_ids = wp_list_pluck($posts, 'ID');
    
    // Get all meta in one query
    $all_meta = get_post_meta($post_ids);
    
    // Get all authors in one query
    $author_ids = array_unique(wp_list_pluck($posts, 'post_author'));
    $authors = get_users(['include' => $author_ids]);
    $authors_by_id = wp_list_pluck($authors, null, 'ID');
    
    foreach ($posts as $post) {
        $meta = $all_meta[$post->ID]['custom_field'][0] ?? '';
        $author = $authors_by_id[$post->post_author] ?? null;
        
        dump("Post {$post->ID}: {$meta}");
    }
    
    dump_query_analysis(); // Much fewer queries
    stop_query_analysis();
}
```

#### Debugging Complex WP_Query
```php
function debug_complex_query() {
    $args = [
        'post_type' => 'product',
        'posts_per_page' => 12,
        'meta_query' => [
            'relation' => 'AND',
            [
                'key' => 'featured',
                'value' => 'yes',
                'compare' => '='
            ],
            [
                'key' => 'price',
                'value' => [10, 100],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN'
            ]
        ],
        'tax_query' => [
            [
                'taxonomy' => 'product_category',
                'field' => 'slug',
                'terms' => 'electronics'
            ]
        ],
        'orderby' => 'meta_value_num',
        'meta_key' => 'price',
        'order' => 'ASC'
    ];
    
    dump('Query arguments:', $args);
    
    $query = new WP_Query($args);
    
    dump_wp_query($query);
    
    if (!$query->have_posts()) {
        dump('No posts found. Debugging...');
        
        // Test individual parts
        $test_query = new WP_Query(['post_type' => 'product']);
        dump('Basic post type query:', $test_query->found_posts . ' posts');
        
        // Test meta query
        $meta_query = new WP_Query([
            'post_type' => 'product',
            'meta_query' => $args['meta_query']
        ]);
        dump('With meta query:', $meta_query->found_posts . ' posts');
        
        // Test tax query
        $tax_query = new WP_Query([
            'post_type' => 'product',
            'tax_query' => $args['tax_query']
        ]);
        dump('With tax query:', $tax_query->found_posts . ' posts');
    }
}
```

### 4. User & Permissions Debugging

#### Debugging User Capabilities
```php
function debug_user_permissions($user_id, $capability) {
    dump_user($user_id);
    dump_caps($user_id);
    
    $user = get_user_by('id', $user_id);
    
    dump([
        'user_can' => user_can($user_id, $capability),
        'current_user_can' => current_user_can($capability),
        'user_roles' => $user->roles,
        'capability_exists' => array_key_exists($capability, $user->allcaps),
        'capability_value' => $user->allcaps[$capability] ?? 'not set'
    ]);
    
    // Check role capabilities
    foreach ($user->roles as $role_name) {
        $role = get_role($role_name);
        dump("Role {$role_name} capabilities:", $role->capabilities);
    }
}

// Usage
add_action('admin_init', function() {
    if (isset($_GET['debug_user'])) {
        debug_user_permissions(get_current_user_id(), 'edit_posts');
    }
});
```

#### Debugging Custom Capabilities
```php
function debug_custom_capability() {
    $capability = 'manage_products';
    
    // Add capability to administrator role
    $admin_role = get_role('administrator');
    $admin_role->add_cap($capability);
    
    // Test the capability
    dump([
        'capability_added' => $admin_role->has_cap($capability),
        'current_user_can' => current_user_can($capability),
        'admin_role_caps' => $admin_role->capabilities
    ]);
    
    // Debug for specific user
    $user = wp_get_current_user();
    dump("User {$user->ID} capabilities:", $user->allcaps);
}
```

### 5. AJAX & REST API Debugging

#### Debugging AJAX Requests
```php
add_action('wp_ajax_my_action', function() {
    dump_wp(); // WordPress context
    dump('AJAX data received:', $_POST);
    
    // Validate nonce
    if (!wp_verify_nonce($_POST['nonce'], 'my_action_nonce')) {
        dump('Nonce verification failed');
        wp_die('Security check failed');
    }
    
    // Process data
    $data = sanitize_text_field($_POST['data']);
    dump('Sanitized data:', $data);
    
    $result = process_my_data($data);
    dump('Processing result:', $result);
    
    wp_send_json_success($result);
});

// Frontend AJAX debugging
add_action('wp_footer', function() {
    if (WP_DEBUG) {
        ?>
        <script>
        jQuery(document).ready(function($) {
            $('#my-button').click(function() {
                console.log('AJAX request starting...');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'my_action',
                        nonce: '<?php echo wp_create_nonce('my_action_nonce'); ?>',
                        data: 'test data'
                    },
                    success: function(response) {
                        console.log('AJAX success:', response);
                    },
                    error: function(xhr, status, error) {
                        console.log('AJAX error:', error);
                        console.log('Response:', xhr.responseText);
                    }
                });
            });
        });
        </script>
        <?php
    }
});
```

#### Debugging REST API Endpoints
```php
add_action('rest_api_init', function() {
    register_rest_route('myplugin/v1', '/products', [
        'methods' => 'GET',
        'callback' => 'debug_products_endpoint',
        'permission_callback' => '__return_true'
    ]);
});

function debug_products_endpoint($request) {
    dump_rest_request();
    
    $params = $request->get_params();
    dump('Request parameters:', $params);
    
    // Validate parameters
    $per_page = isset($params['per_page']) ? intval($params['per_page']) : 10;
    $page = isset($params['page']) ? intval($params['page']) : 1;
    
    dump('Validated params:', compact('per_page', 'page'));
    
    // Get products
    $products = get_posts([
        'post_type' => 'product',
        'posts_per_page' => $per_page,
        'paged' => $page
    ]);
    
    dump('Found products:', count($products));
    
    return rest_ensure_response([
        'products' => $products,
        'total' => wp_count_posts('product')->publish,
        'page' => $page,
        'per_page' => $per_page
    ]);
}
```

### 6. Performance Optimization

#### Profiling Page Load Performance
```php
// In functions.php
add_action('init', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        start_wp_profiling();
        wp_checkpoint('WordPress init');
    }
}, 1);

add_action('wp_loaded', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        wp_checkpoint('WordPress loaded');
    }
});

add_action('template_redirect', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        wp_checkpoint('Template redirect');
    }
});

add_action('wp_head', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        wp_checkpoint('wp_head start');
    }
}, 1);

add_action('wp_head', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        wp_checkpoint('wp_head end');
    }
}, 999);

add_action('wp_footer', function() {
    if (WP_DEBUG && current_user_can('administrator')) {
        wp_checkpoint('Footer reached');
        stop_wp_profiling();
    }
}, 999);
```

#### Profiling Plugin Performance
```php
class MyPlugin {
    public function __construct() {
        if (WP_DEBUG) {
            start_wp_profiling();
            wp_checkpoint('Plugin constructor');
        }
        
        add_action('init', [$this, 'init']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
    }
    
    public function init() {
        if (WP_DEBUG) {
            wp_checkpoint('Plugin init start');
        }
        
        // Plugin initialization code
        $this->setup_post_types();
        $this->setup_taxonomies();
        $this->setup_meta_boxes();
        
        if (WP_DEBUG) {
            wp_checkpoint('Plugin init complete');
        }
    }
    
    public function enqueue_scripts() {
        if (WP_DEBUG) {
            wp_checkpoint('Enqueue scripts start');
        }
        
        wp_enqueue_script('myplugin-js', plugin_dir_url(__FILE__) . 'js/script.js');
        wp_enqueue_style('myplugin-css', plugin_dir_url(__FILE__) . 'css/style.css');
        
        if (WP_DEBUG) {
            wp_checkpoint('Enqueue scripts complete');
            stop_wp_profiling();
        }
    }
}
```

### 7. Database Query Optimization

#### Before and After Comparison
```php
function compare_query_performance() {
    // Test old method
    dump('=== OLD METHOD ===');
    start_query_analysis();
    
    $posts = get_posts(['numberposts' => 20]);
    foreach ($posts as $post) {
        $meta = get_post_meta($post->ID);
        $author = get_user_by('id', $post->post_author);
    }
    
    dump_query_analysis();
    stop_query_analysis();
    
    // Test new method
    dump('=== NEW METHOD ===');
    start_query_analysis();
    
    $posts = get_posts(['numberposts' => 20]);
    $post_ids = wp_list_pluck($posts, 'ID');
    $author_ids = array_unique(wp_list_pluck($posts, 'post_author'));
    
    // Batch queries
    $all_meta = get_post_meta($post_ids);
    $authors = get_users(['include' => $author_ids]);
    
    dump_query_analysis();
    stop_query_analysis();
}
```

## 💡 Pro Tips

### 1. Conditional Debugging
```php
// Only debug for specific users
$debug_users = ['admin', 'developer'];
if (in_array(wp_get_current_user()->user_login, $debug_users)) {
    dump_wp();
}

// Only debug on specific pages
if (is_single() && get_post_type() === 'product') {
    dump_template();
}

// Only debug with URL parameter
if (isset($_GET['debug'])) {
    start_wp_profiling();
}
```

### 2. Organized Debug Output
```php
function debug_section($title, $callback) {
    dump("=== {$title} ===");
    $callback();
    dump("=== END {$title} ===");
}

debug_section('WordPress State', function() {
    dump_wp();
});

debug_section('Query Information', function() {
    dump_query();
});

debug_section('User Information', function() {
    dump_user();
});
```

### 3. Debug Logging
```php
function debug_log($message, $data = null) {
    if (WP_DEBUG_LOG) {
        $log_entry = '[' . date('Y-m-d H:i:s') . '] ' . $message;
        if ($data !== null) {
            $log_entry .= ': ' . print_r($data, true);
        }
        error_log($log_entry);
    }
    
    if (WP_DEBUG && current_user_can('administrator')) {
        dump($message, $data);
    }
}

// Usage
debug_log('User login attempt', ['user' => $username, 'ip' => $_SERVER['REMOTE_ADDR']]);
```

## 🔗 Related Documentation

- [Basic Dumping](basic-dumping.md) - Core functions
- [WordPress Debugging](wordpress-debugging.md) - WordPress-specific debugging
- [Performance Profiling](profiling.md) - Performance analysis
- [Query Analysis](query-analysis.md) - Database debugging
- [Function Reference](function-reference.md) - Complete function list
