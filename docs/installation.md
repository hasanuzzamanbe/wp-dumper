# Installation & Setup

Get WP Dumper up and running in your WordPress development environment.

## 📦 Installation Methods

### Method 1: Manual Installation

1. **Download or clone** the plugin to your WordPress plugins directory:
   ```bash
   cd wp-content/plugins/
   git clone [repository-url] wp-dumper
   ```

2. **Install Composer dependencies**:
   ```bash
   cd wp-dumper
   composer install
   ```

3. **Activate the plugin** in WordPress admin or via WP-CLI:
   ```bash
   wp plugin activate wp-dumper
   ```

### Method 2: Must-Use Plugin (Recommended for Development)

For development environments, install as a must-use plugin to ensure it loads before other plugins:

1. **Copy to mu-plugins directory**:
   ```bash
   cp wp-dumper/wp-dumper-mu.php wp-content/mu-plugins/
   ```

2. **Install dependencies** (if not already done):
   ```bash
   cd wp-content/plugins/wp-dumper
   composer install
   ```

## ⚙️ Configuration

### Basic Configuration

Add these constants to your `wp-config.php` for optimal debugging:

```php
// Enable WordPress debugging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Optional: Enable query debugging
define('SAVEQUERIES', true);
```

### Dump Server Setup (Optional)

For network-based dump viewing, set up the dump server:

1. **Install the dump server globally**:
   ```bash
   composer global require symfony/var-dumper
   ```

2. **Start the dump server**:
   ```bash
   # Default port (9913)
   var-dump-server

   # Custom port
   var-dump-server --host=127.0.0.1:9914
   ```

3. **Or use the included server script**:
   ```bash
   cd wp-content/plugins/wp-dumper
   php vendor/bin/var-dump-server
   ```

## 🎯 Verification

### Test Basic Functionality

Add this to your theme's `functions.php` or any plugin file:

```php
// Test basic dumping
add_action('init', function() {
    if (current_user_can('administrator')) {
        dump('WP Dumper is working!');
    }
});
```

### Test WordPress-Specific Functions

```php
// Test WordPress debugging functions
add_action('wp_footer', function() {
    if (current_user_can('administrator')) {
        dump_wp(); // WordPress context
        perf(); // Performance metrics
    }
});
```

### Test Profiling

```php
// Test profiling functionality
add_action('init', function() {
    if (current_user_can('administrator')) {
        start_wp_profiling();
        wp_checkpoint('Init complete');
    }
});

add_action('wp_footer', function() {
    if (current_user_can('administrator')) {
        wp_checkpoint('Footer reached');
        stop_wp_profiling();
    }
});
```

## 🔧 Environment-Specific Setup

### Development Environment

```php
// wp-config.php for development
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
define('SCRIPT_DEBUG', true);
define('SAVEQUERIES', true);

// Optional: Auto-start dump server
// Only if you have it installed globally
if (WP_DEBUG && !wp_doing_ajax() && !defined('REST_REQUEST')) {
    // Start dump server automatically (requires global installation)
    // exec('var-dump-server > /dev/null 2>&1 &');
}
```

### Staging Environment

```php
// wp-config.php for staging
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);

// Restrict debugging to specific users
if (WP_DEBUG && is_user_logged_in()) {
    $debug_users = ['admin', 'developer']; // Add your usernames
    $current_user = wp_get_current_user();
    if (!in_array($current_user->user_login, $debug_users)) {
        define('WP_DEBUG', false);
    }
}
```

### Production Environment

```php
// wp-config.php for production
define('WP_DEBUG', false);
define('WP_DEBUG_LOG', false);
define('WP_DEBUG_DISPLAY', false);

// Completely disable WP Dumper in production
if (!WP_DEBUG) {
    // Deactivate plugin if accidentally activated
    add_action('admin_init', function() {
        if (is_plugin_active('wp-dumper/wp-dumper.php')) {
            deactivate_plugins('wp-dumper/wp-dumper.php');
        }
    });
}
```

## 🚀 Performance Considerations

### Memory Limits

For heavy debugging sessions, you might need to increase PHP memory:

```php
// wp-config.php
ini_set('memory_limit', '512M');
// or
define('WP_MEMORY_LIMIT', '512M');
```

### Query Limits

When using query analysis, be aware of query logging overhead:

```php
// Limit query logging to specific scenarios
if (WP_DEBUG && isset($_GET['debug_queries'])) {
    start_query_analysis();
}
```

## 🔒 Security Considerations

### Restrict Access

Always restrict debugging to authorized users:

```php
// Only allow debugging for administrators
add_action('init', function() {
    if (!current_user_can('administrator')) {
        // Disable all dump functions for non-admins
        if (!function_exists('dump')) {
            function dump(...$vars) { /* no-op */ }
        }
        if (!function_exists('dd')) {
            function dd(...$vars) { /* no-op */ }
        }
    }
});
```

### Environment Detection

```php
// Only enable in development environments
$allowed_hosts = ['localhost', '127.0.0.1', '.local', '.dev', '.test'];
$current_host = $_SERVER['HTTP_HOST'] ?? '';

$is_dev_environment = false;
foreach ($allowed_hosts as $host) {
    if (strpos($current_host, $host) !== false) {
        $is_dev_environment = true;
        break;
    }
}

if (!$is_dev_environment) {
    define('WP_DEBUG', false);
}
```

## 🛠️ Troubleshooting Installation

### Common Issues

#### 1. "Composer autoloader is missing"

**Problem:** Plugin shows error about missing autoloader.

**Solution:**
```bash
cd wp-content/plugins/wp-dumper
composer install
```

#### 2. Functions not available

**Problem:** `dump()` or `dd()` functions not working.

**Solutions:**
- Check if plugin is activated
- Verify Composer dependencies are installed
- Check for PHP errors in error log
- Ensure WordPress is fully loaded before calling functions

#### 3. Dump server not receiving data

**Problem:** Dumps not appearing in dump server.

**Solutions:**
- Verify `WP_DEBUG` is `true`
- Check if dump server is running on correct port (9913)
- Test server connectivity: `curl http://localhost:9913`
- Check firewall settings

#### 4. Memory or timeout issues

**Problem:** Plugin causes memory exhaustion or timeouts.

**Solutions:**
- Increase PHP memory limit
- Avoid dumping very large objects
- Use conditional debugging
- Limit profiling to specific scenarios

### Debug Installation

```php
// Add to functions.php to debug installation
add_action('admin_notices', function() {
    if (current_user_can('administrator')) {
        echo '<div class="notice notice-info">';
        echo '<p>WP Dumper Status:</p>';
        echo '<ul>';
        echo '<li>Plugin Active: ' . (is_plugin_active('wp-dumper/wp-dumper.php') ? 'Yes' : 'No') . '</li>';
        echo '<li>Autoloader: ' . (file_exists(WP_PLUGIN_DIR . '/wp-dumper/vendor/autoload.php') ? 'Found' : 'Missing') . '</li>';
        echo '<li>Functions Available: ' . (function_exists('dump') ? 'Yes' : 'No') . '</li>';
        echo '<li>WP_DEBUG: ' . (WP_DEBUG ? 'Enabled' : 'Disabled') . '</li>';
        echo '</ul>';
        echo '</div>';
    }
});
```

## 📋 Requirements

### Minimum Requirements

- **WordPress:** 5.0+
- **PHP:** 7.4+
- **Composer:** For dependency management

### Recommended Requirements

- **WordPress:** 6.0+
- **PHP:** 8.0+
- **Memory:** 256MB+ for heavy debugging
- **Development Environment:** Local or staging only

## 🔗 Next Steps

After installation:

1. **Read the [Basic Usage Guide](basic-usage.md)**
2. **Explore [WordPress Debugging Functions](wordpress-debugging.md)**
3. **Learn about [Performance Profiling](profiling.md)**
4. **Check out [Examples & Recipes](examples.md)**

## 🆘 Getting Help

If you encounter issues:

1. Check the [Troubleshooting Guide](troubleshooting.md)
2. Review the [Function Reference](function-reference.md)
3. Examine your error logs
4. Verify your environment meets requirements
