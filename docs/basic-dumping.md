# Basic Dumping Functions

The core functionality of WP Dumper revolves around two main functions: `dump()` and `dd()` (dump and die). These provide Laravel-style variable dumping with beautiful, readable output.

## 🎯 Core Functions

### `dump()` - Display Variables

The `dump()` function displays variables in a formatted, readable way without stopping execution.

```php
$user = get_current_user();
$posts = get_posts(['numberposts' => 5]);

// Dump single variable
dump($user);

// Dump multiple variables
dump($user, $posts);

// Dump with context
dump('User data:', $user);
dump('Recent posts:', $posts);
```

### `dd()` - Dump and Die

The `dd()` function dumps variables and then stops execution. Perfect for debugging specific points in your code.

```php
$data = some_complex_function();

// Dump and stop execution
dd($data);

// This line will never be reached
echo "This won't execute";
```

## 🎨 Output Modes

### Browser Display (Default)

Variables are displayed directly in your browser with styled HTML output:

```php
dump($array);
// Displays formatted HTML in browser
```

### Network Display (Dump Server)

When `WP_DEBUG=true`, dumps are sent to a local dump server for network viewing:

```php
// With WP_DEBUG=true in wp-config.php
dump($data); // Sends to localhost:9913 dump server
```

### REST API Support

For REST API endpoints, `dd()` returns clean JSON responses:

```php
// In REST API endpoint
add_action('rest_api_init', function() {
    register_rest_route('myplugin/v1', '/debug', [
        'methods' => 'POST',
        'callback' => function($request) {
            $data = $request->get_json_params();
            
            // Returns clean JSON response
            dd($data);
        }
    ]);
});
```

## 🔧 Advanced Usage

### Dumping Different Data Types

```php
// Arrays
$array = ['name' => 'John', 'age' => 30];
dump($array);

// Objects
$user = new WP_User(1);
dump($user);

// WordPress objects (special handling)
$post = get_post(123);
dump($post); // Shows ID, title, type, status

$query = new WP_Query(['post_type' => 'product']);
dump($query); // Shows found_posts, query_vars, etc.

// Complex nested data
$complex = [
    'user' => get_current_user(),
    'posts' => get_posts(['numberposts' => 3]),
    'options' => [
        'theme' => get_option('stylesheet'),
        'admin_email' => get_option('admin_email')
    ]
];
dump($complex);
```

### Conditional Dumping

```php
// Only dump in development
if (WP_DEBUG) {
    dump($debug_data);
}

// Only dump for specific users
if (current_user_can('administrator')) {
    dump($admin_data);
}

// Only dump on specific pages
if (is_single() && get_post_type() === 'product') {
    dump($product_data);
}
```

### Dumping with Labels

```php
// Add descriptive labels
dump('User Information:', $user);
dump('Query Results:', $posts);
dump('Configuration:', $config);

// Multiple labeled dumps
dump('Before processing:', $raw_data);
$processed_data = process_data($raw_data);
dump('After processing:', $processed_data);
```

## 🎯 Common Use Cases

### 1. Debugging Function Parameters

```php
function my_custom_function($param1, $param2, $options = []) {
    // Debug what was passed in
    dump('Function called with:', compact('param1', 'param2', 'options'));
    
    // Your function logic
    return process_data($param1, $param2, $options);
}
```

### 2. Debugging WordPress Hooks

```php
add_action('init', function() {
    dump('WordPress init action fired');
    dump('Current user:', wp_get_current_user());
});

add_filter('the_content', function($content) {
    dump('Content before filter:', $content);
    
    $modified_content = modify_content($content);
    
    dump('Content after filter:', $modified_content);
    
    return $modified_content;
});
```

### 3. Debugging AJAX Requests

```php
add_action('wp_ajax_my_action', function() {
    $data = $_POST;
    
    // Debug AJAX data
    dump('AJAX request data:', $data);
    
    $result = process_ajax_data($data);
    
    dump('AJAX result:', $result);
    
    wp_send_json_success($result);
});
```

### 4. Debugging Custom Queries

```php
$args = [
    'post_type' => 'product',
    'meta_query' => [
        [
            'key' => 'featured',
            'value' => 'yes'
        ]
    ]
];

// Debug query arguments
dump('Query args:', $args);

$query = new WP_Query($args);

// Debug query results
dump('Query object:', $query);
dump('Found posts:', $query->found_posts);
dump('SQL query:', $query->request);
```

### 5. Debugging Form Processing

```php
if ($_POST) {
    // Debug form submission
    dump('Form data received:', $_POST);
    
    $sanitized = sanitize_form_data($_POST);
    dump('Sanitized data:', $sanitized);
    
    $validation_errors = validate_form_data($sanitized);
    if ($validation_errors) {
        dump('Validation errors:', $validation_errors);
    }
}
```

## 🚫 What NOT to Dump

### Sensitive Information

```php
// DON'T dump sensitive data
// dump($_POST['password']); // ❌
// dump(get_option('secret_key')); // ❌

// Instead, dump safely
dump('Password length:', strlen($_POST['password'])); // ✅
dump('Has secret key:', !empty(get_option('secret_key'))); // ✅
```

### Very Large Objects

```php
// Be careful with large datasets
$all_posts = get_posts(['numberposts' => -1]); // Could be thousands
// dump($all_posts); // ❌ Might crash browser

// Instead, dump summaries
dump('Total posts:', count($all_posts)); // ✅
dump('First 5 posts:', array_slice($all_posts, 0, 5)); // ✅
```

## 🎨 Styling and Appearance

### Default Styling

Dumps appear with dark styling by default:
- Dark background (#18171B)
- Syntax highlighting
- Collapsible arrays and objects
- File and line number information

### Customizing Appearance

The output uses Symfony VarDumper's HTML dumper, which provides:
- Color-coded output
- Interactive expanding/collapsing
- Copy-to-clipboard functionality
- Source file links (when available)

## 🔍 Debugging Tips

### 1. Use Descriptive Labels

```php
// Good
dump('User permissions after role change:', $user->allcaps);

// Better than
dump($user->allcaps);
```

### 2. Dump at Multiple Points

```php
dump('Input data:', $input);

$step1 = process_step_one($input);
dump('After step 1:', $step1);

$step2 = process_step_two($step1);
dump('After step 2:', $step2);

$final = finalize_processing($step2);
dump('Final result:', $final);
```

### 3. Use `dd()` to Stop at Problem Points

```php
if ($error_condition) {
    dd('Error occurred:', $error_data);
}
```

### 4. Combine with WordPress Functions

```php
// Debug current page context
dump([
    'is_home' => is_home(),
    'is_single' => is_single(),
    'is_page' => is_page(),
    'post_type' => get_post_type(),
    'queried_object' => get_queried_object()
]);
```

## 🔗 Related Functions

- [`dump_wp()`](wordpress-debugging.md#dump_wp) - WordPress context
- [`dump_query()`](wordpress-debugging.md#dump_query) - Query information
- [`dump_user()`](wordpress-debugging.md#dump_user) - User information
- [`start_wp_profiling()`](profiling.md) - Performance profiling

## 🔗 Next Steps

- Learn about [WordPress-specific debugging functions](wordpress-debugging.md)
- Explore [performance profiling](profiling.md)
- Check out [query analysis](query-analysis.md)
- See [real-world examples](examples.md)
