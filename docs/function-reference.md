# Function Reference

Complete reference of all available debugging functions in WP Dumper.

## 🎯 Core Dumping Functions

### `dump(...$vars)`
Displays variables in formatted output without stopping execution.

```php
dump($variable);
dump($var1, $var2, $var3);
dump('Label:', $variable);
```

**Parameters:**
- `...$vars` - Any number of variables to dump

**Output:** Formatted HTML display (or network dump server if `WP_DEBUG=true`)

---

### `dd(...$vars)`
Dumps variables and stops execution (dump and die).

```php
dd($variable);
dd($var1, $var2);
```

**Parameters:**
- `...$vars` - Any number of variables to dump

**Output:** 
- **Regular requests:** Formatted HTML display, then `die()`
- **REST API:** Clean JSON response with dump data
- **AJAX:** HTML output, then `die()`

**Note:** Does NOT send to network dump server (only direct output)

---

## 🔧 Performance Profiling

### `start_wp_profiling()`
Starts performance profiling with hook and query logging.

```php
$profiler = start_wp_profiling();
```

**Returns:** `WP_Debug_Profiler` instance

---

### `stop_wp_profiling()`
Stops profiling and dumps comprehensive performance results.

```php
stop_wp_profiling();
```

**Output:** Performance analysis including:
- Execution summary (time, memory, queries)
- Checkpoint data
- Top hooks by call count
- Slow query detection
- Query summary by type

---

### `wp_checkpoint($label)`
Adds a performance checkpoint with the given label.

```php
wp_checkpoint('After database queries');
wp_checkpoint('Template rendering complete');
```

**Parameters:**
- `$label` (string) - Descriptive label for the checkpoint

**Returns:** `WP_Debug_Profiler` instance

---

## 📊 Query Analysis

### `start_query_analysis()`
Starts comprehensive database query logging and analysis.

```php
start_query_analysis();
```

---

### `stop_query_analysis()`
Stops query logging.

```php
stop_query_analysis();
```

---

### `dump_query_analysis()`
Analyzes and dumps all logged queries with performance insights.

```php
dump_query_analysis();
```

**Output:** Query analysis including:
- Query summary (total, unique, duplicates)
- Queries by type (SELECT, INSERT, UPDATE, DELETE)
- Queries by table
- Duplicate query detection
- Slow query identification
- N+1 problem detection

---

## 🎯 WordPress-Specific Debugging

### `dump_wp()` / `dump_wp_context()`
Dumps current WordPress context and state.

```php
dump_wp();
dump_wp_context(); // Same function
```

**Output:** WordPress context including:
- Request information (method, URI, user agent)
- WordPress state (current action/filter, AJAX, REST, cron status)
- Query variables
- Current post information
- Current user
- Memory usage and execution time

---

### `ddwp(...$vars)`
Dumps WordPress context and optionally additional variables, then dies.

```php
ddwp();
ddwp($additional_data);
```

**Parameters:**
- `...$vars` - Optional additional variables to dump

---

### `dump_query($query = null)`
Dumps WordPress query information and performance metrics.

```php
dump_query(); // Current global $wp_query
dump_query($custom_query); // Specific query
```

**Parameters:**
- `$query` (WP_Query|null) - Query object to analyze (defaults to global `$wp_query`)

**Output:**
- Main query object
- Last SQL query
- Query count and execution time
- Memory usage

---

### `dump_wp_query($query = null)`
Detailed analysis of a WP_Query object.

```php
dump_wp_query($query);
```

**Parameters:**
- `$query` (WP_Query|null) - Query object to analyze

**Output:**
- Query variables
- Generated SQL request
- Found posts and pagination info
- WordPress conditional functions (is_home, is_single, etc.)
- Queried object

---

### `dump_user($user_id = null)`
Dumps user information and capabilities.

```php
dump_user(); // Current user
dump_user(123); // Specific user
```

**Parameters:**
- `$user_id` (int|null) - User ID (defaults to current user)

**Output:**
- User basic info (ID, login, email, display name)
- User roles
- User capabilities
- User meta data

---

### `dump_caps($user_id = null)`
Dumps detailed user capability analysis.

```php
dump_caps(); // Current user
dump_caps(123); // Specific user
```

**Parameters:**
- `$user_id` (int|null) - User ID (defaults to current user)

**Output:**
- User information
- All user capabilities
- Role-based capabilities breakdown

---

### `dump_hooks($hook_name = null)`
Dumps WordPress hooks and their callbacks.

```php
dump_hooks(); // All hooks
dump_hooks('wp_head'); // Specific hook
```

**Parameters:**
- `$hook_name` (string|null) - Specific hook name (defaults to all hooks)

**Output:**
- Hook callbacks by priority
- Function names and accepted arguments

---

### `dump_template()`
Dumps template hierarchy and page type information.

```php
dump_template();
```

**Output:**
- Current template file
- Template hierarchy for current page type
- WordPress conditional functions (is_home, is_single, etc.)
- Queried object

---

### `dump_performance()`
Dumps current performance metrics.

```php
dump_performance();
```

**Output:**
- Execution time
- Memory usage (current, peak, limit)
- Database query count and time
- Loaded plugins count

---

### `dump_wp_env()`
Dumps WordPress environment and configuration.

```php
dump_wp_env();
```

**Output:**
- WordPress version and configuration
- PHP version and settings
- Server information
- Active plugins and theme
- Important WordPress constants

---

### `dump_wp_constants($pattern = null)`
Dumps WordPress-related constants.

```php
dump_wp_constants(); // All WP constants
dump_wp_constants('DB_'); // Constants matching pattern
```

**Parameters:**
- `$pattern` (string|null) - Pattern to filter constants

**Output:** WordPress constants (WP_, DB_, AUTH_, etc.)

---

### `dump_rest()`
Dumps REST API information and status.

```php
dump_rest();
```

**Output:**
- REST API status and configuration
- Available routes
- Current user REST capabilities

---

### `dump_rest_request()`
Dumps current REST API request information.

```php
dump_rest_request();
```

**Output:**
- Request method and route
- Request parameters and body
- Headers and authentication status

**Note:** Only works within REST API requests

---

### `dump_rest_route()`
Dumps REST API route information.

```php
dump_rest_route();
```

**Output:**
- Current request URI
- Matching routes and handlers
- Total available routes

---

### `dump_widgets($sidebar = null)`
Dumps WordPress widget information.

```php
dump_widgets(); // All widgets
dump_widgets('sidebar-1'); // Specific sidebar
```

**Parameters:**
- `$sidebar` (string|null) - Specific sidebar name

**Output:**
- Registered sidebars
- Active widgets
- Widget configuration

---

## 🚀 Quick Shortcuts

### `perf()`
Shortcut for `dump_performance()`.

```php
perf();
```

---

### `env()`
Shortcut for `dump_wp_env()`.

```php
env();
```

---

### `queries()`
Shortcut for `dump_query()`.

```php
queries();
```

---

## 🔗 Related Documentation

- [Basic Dumping](basic-dumping.md) - Core dump functions
- [WordPress Debugging](wordpress-debugging.md) - WordPress-specific functions
- [Performance Profiling](profiling.md) - Profiling functions
- [Query Analysis](query-analysis.md) - Query analysis functions
