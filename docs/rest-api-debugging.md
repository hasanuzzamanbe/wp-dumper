# REST API Debugging with WP Dumper

WP Dumper now fully supports debugging REST API and AJAX requests while maintaining the same behavior as regular dumps.

## How It Works

When WP Dumper detects a REST API or AJAX request, it automatically:

1. **Server Mode (WP_DEBUG=true)**: Sends dumps to the dump server (localhost:9913) just like regular requests
2. **Fallback Mode (WP_DEBUG=false)**: Logs dumps to PHP error_log instead of buffering for footer output
3. **`dd()` behavior**:
   - **With WP_DEBUG=true**: Sends to dump server and dies with appropriate response
   - **With WP_DEBUG=false**: Logs to error_log and dies with appropriate response
4. **No API response modification**: Your REST API responses remain clean and unmodified

## REST API Debugging Functions

### Basic Debugging

```php
// In your REST API endpoint
add_action('rest_api_init', function() {
    register_rest_route('myplugin/v1', '/test', [
        'methods' => 'GET',
        'callback' => function($request) {
            // Debug the request
            dump($request->get_params());
            
            // Debug current user
            dump_user();
            
            // Your API logic here
            $result = ['message' => 'Hello World'];
            
            return $result;
        }
    ]);
});
```

### Advanced REST Debugging

```php
// Debug REST request details
dump_rest_request(); // Shows method, route, params, headers, auth status

// Debug REST route information
dump_rest_route(); // Shows matching routes and handlers

// Debug REST environment
dump_rest(); // Shows REST API configuration and status
```

## Example Output

### With WP_DEBUG=true (Server Mode)
Dumps are sent to your dump server (localhost:9913) just like regular page requests. Your REST API response remains clean:

```json
{
  "message": "Hello World"
}
```

And in your dump server, you'll see the formatted dump output.

### With WP_DEBUG=false (Fallback Mode)
Regular `dump()` calls are logged to PHP error_log, while `dd()` calls also log to error_log. Check your error log file:

```
[2024-01-15 14:30:25] [WP Dumper REST] wp-content/plugins/myplugin/api.php:15
array(2) {
  ["param1"] => string(6) "value1"
  ["param2"] => string(6) "value2"
}
```

Your REST API response remains clean:
```json
{
  "message": "Hello World"
}
```

## Using `dd()` in REST API

```php
add_action('rest_api_init', function() {
    register_rest_route('myplugin/v1', '/debug', [
        'methods' => 'POST',
        'callback' => function($request) {
            $data = $request->get_json_params();

            // With WP_DEBUG=true: sends to dump server and stops execution
            // With WP_DEBUG=false: logs to error_log and stops execution
            dd($data);

            // This line will never be reached
            return ['status' => 'success'];
        }
    ]);
});
```

**What happens:**
1. **Error Log Entry**: The dump data is logged to PHP error_log
2. **Response**: Returns a 500 error with message "Debug dump and die - check error_log"
3. **Execution Stops**: No further code is executed

**Error Log:**
```
[2024-01-15 14:30:25] [WP Dumper DD REST] wp-content/plugins/myplugin/api.php:25 (var 1)
array(2) {
  ["param1"] => string(6) "value1"
  ["param2"] => string(6) "value2"
}
```

## WordPress Object Handling

WP Dumper automatically converts WordPress objects to JSON-safe format:

```php
// These objects are automatically simplified for JSON
dump(get_current_user()); // Converts WP_User to essential fields
dump(get_post(123)); // Converts WP_Post to essential fields
dump(new WP_Query(['post_type' => 'product'])); // Converts WP_Query to summary
```

## Testing REST API Debugging

### Using cURL

```bash
# Test with cURL - response will be clean
curl -X GET "https://yoursite.com/wp-json/myplugin/v1/test?param1=value1" \
  -H "Content-Type: application/json"

# Check your error log or dump server for debug output
tail -f /path/to/error.log
```

### Using JavaScript

```javascript
// Test with fetch - response will be clean JSON
fetch('/wp-json/myplugin/v1/test', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
    },
    body: JSON.stringify({
        param1: 'value1',
        param2: 'value2'
    })
})
.then(response => response.json())
.then(data => {
    // Clean response, no dump data mixed in
    console.log('Response:', data);

    // Check browser console or dump server for debug output
});
```

### Using WordPress REST API Client

```php
// Test from another WordPress site or script
$response = wp_remote_get('https://yoursite.com/wp-json/myplugin/v1/test');
$body = wp_remote_retrieve_body($response);
$data = json_decode($body, true);

if (isset($data['_wp_dumps'])) {
    foreach ($data['_wp_dumps'] as $dump) {
        error_log('REST Dump: ' . print_r($dump, true));
    }
}
```

## Performance Considerations

- **Automatic Truncation**: Large objects are automatically truncated to prevent huge responses
- **Depth Limiting**: Object recursion is limited to prevent infinite loops
- **Array Limiting**: Large arrays are truncated with indicators
- **String Limiting**: Very long strings are truncated

## Best Practices

1. **Remove dumps before production**: Always remove debug calls before deploying
2. **Use headers for quick debugging**: Check response headers in browser dev tools
3. **Use `dump()` for non-breaking debugging**: Continues execution
4. **Use `dd()` for immediate debugging**: Stops execution with error response
5. **Check `_wp_dumps` in response**: Look for the dumps array in JSON responses

## Common Use Cases

### Debug Authentication Issues
```php
register_rest_route('myplugin/v1', '/protected', [
    'methods' => 'GET',
    'permission_callback' => function() {
        dump_user(); // Debug current user
        dump_caps(); // Debug capabilities
        return current_user_can('edit_posts');
    },
    'callback' => 'my_callback'
]);
```

### Debug Request Data
```php
register_rest_route('myplugin/v1', '/process', [
    'methods' => 'POST',
    'callback' => function($request) {
        dump_rest_request(); // Full request details
        dump($request->get_json_params()); // JSON body
        dump($request->get_headers()); // Request headers
        
        // Process the request...
    }
]);
```

### Debug Database Queries in API
```php
register_rest_route('myplugin/v1', '/data', [
    'methods' => 'GET',
    'callback' => function($request) {
        start_query_analysis();
        
        // Your database operations
        $posts = get_posts(['post_type' => 'product']);
        
        dump_query_analysis(); // Shows query performance
        
        return $posts;
    }
]);
```

This makes debugging REST APIs much more efficient and doesn't break your API responses!
