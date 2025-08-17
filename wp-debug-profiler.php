<?php
/**
 * WordPress Debug Profiler
 * 
 * Advanced debugging and profiling tools for WordPress development
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Debug_Profiler {
    private static $instance = null;
    private $start_time;
    private $start_memory;
    private $checkpoints = [];
    private $hook_calls = [];
    private $query_log = [];
    private $is_profiling = false;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->start_time = microtime(true);
        $this->start_memory = memory_get_usage(true);
    }

    /**
     * Start profiling
     */
    public function start_profiling() {
        $this->is_profiling = true;
        $this->hook_query_logging();
        $this->hook_action_logging();
        return $this;
    }

    /**
     * Stop profiling and dump results
     */
    public function stop_profiling() {
        $this->is_profiling = false;
        $this->dump_profile_results();
        return $this;
    }

    /**
     * Add a checkpoint for performance tracking
     */
    public function checkpoint($label) {
        $this->checkpoints[] = [
            'label' => $label,
            'time' => microtime(true),
            'memory' => memory_get_usage(true),
            'queries' => get_num_queries(),
        ];
        return $this;
    }

    /**
     * Hook into WordPress query logging
     */
    private function hook_query_logging() {
        add_filter('query', [$this, 'log_query']);
    }

    /**
     * Log database queries
     */
    public function log_query($query) {
        if ($this->is_profiling) {
            $this->query_log[] = [
                'query' => $query,
                'time' => microtime(true),
                'backtrace' => wp_debug_backtrace_summary(null, 3),
            ];
        }
        return $query;
    }

    /**
     * Hook into WordPress action logging
     */
    private function hook_action_logging() {
        add_action('all', [$this, 'log_hook_call']);
    }

    /**
     * Log hook calls
     */
    public function log_hook_call($hook_name) {
        if ($this->is_profiling && !in_array($hook_name, ['all', 'gettext', 'gettext_with_context'])) {
            if (!isset($this->hook_calls[$hook_name])) {
                $this->hook_calls[$hook_name] = 0;
            }
            $this->hook_calls[$hook_name]++;
        }
    }

    /**
     * Dump profiling results
     */
    private function dump_profile_results() {
        $end_time = microtime(true);
        $end_memory = memory_get_usage(true);

        $results = [
            'execution_summary' => [
                'total_time' => round($end_time - $this->start_time, 4) . 's',
                'memory_used' => size_format($end_memory - $this->start_memory),
                'peak_memory' => size_format(memory_get_peak_usage(true)),
                'total_queries' => get_num_queries(),
            ],
            'checkpoints' => $this->format_checkpoints(),
            'top_hooks' => $this->get_top_hooks(10),
            'slow_queries' => $this->get_slow_queries(),
            'query_summary' => $this->get_query_summary(),
        ];

        dump($results);
    }

    /**
     * Format checkpoint data
     */
    private function format_checkpoints() {
        $formatted = [];
        $prev_time = $this->start_time;
        $prev_memory = $this->start_memory;
        $prev_queries = 0;

        foreach ($this->checkpoints as $checkpoint) {
            $formatted[] = [
                'label' => $checkpoint['label'],
                'elapsed_time' => round($checkpoint['time'] - $prev_time, 4) . 's',
                'memory_diff' => size_format($checkpoint['memory'] - $prev_memory),
                'queries_diff' => $checkpoint['queries'] - $prev_queries,
                'total_time' => round($checkpoint['time'] - $this->start_time, 4) . 's',
                'total_memory' => size_format($checkpoint['memory']),
            ];

            $prev_time = $checkpoint['time'];
            $prev_memory = $checkpoint['memory'];
            $prev_queries = $checkpoint['queries'];
        }

        return $formatted;
    }

    /**
     * Get most called hooks
     */
    private function get_top_hooks($limit = 10) {
        arsort($this->hook_calls);
        return array_slice($this->hook_calls, 0, $limit, true);
    }

    /**
     * Analyze slow queries (placeholder - would need query timing)
     */
    private function get_slow_queries() {
        // This is a simplified version - in reality you'd need to time queries
        $slow_queries = [];
        foreach ($this->query_log as $query_data) {
            if (strlen($query_data['query']) > 500) { // Long queries are often slow
                $slow_queries[] = [
                    'query' => substr($query_data['query'], 0, 200) . '...',
                    'backtrace' => $query_data['backtrace'],
                ];
            }
        }
        return array_slice($slow_queries, 0, 5);
    }

    /**
     * Get query summary
     */
    private function get_query_summary() {
        $summary = [
            'total_queries' => count($this->query_log),
            'select_queries' => 0,
            'insert_queries' => 0,
            'update_queries' => 0,
            'delete_queries' => 0,
        ];

        foreach ($this->query_log as $query_data) {
            $query = strtoupper($query_data['query']);
            if (strpos($query, 'SELECT') === 0) {
                $summary['select_queries']++;
            } elseif (strpos($query, 'INSERT') === 0) {
                $summary['insert_queries']++;
            } elseif (strpos($query, 'UPDATE') === 0) {
                $summary['update_queries']++;
            } elseif (strpos($query, 'DELETE') === 0) {
                $summary['delete_queries']++;
            }
        }

        return $summary;
    }
}

/**
 * Global profiler functions
 */
if (!function_exists('start_wp_profiling')) {
    function start_wp_profiling() {
        return WP_Debug_Profiler::getInstance()->start_profiling();
    }
}

if (!function_exists('stop_wp_profiling')) {
    function stop_wp_profiling() {
        return WP_Debug_Profiler::getInstance()->stop_profiling();
    }
}

if (!function_exists('wp_checkpoint')) {
    function wp_checkpoint($label) {
        return WP_Debug_Profiler::getInstance()->checkpoint($label);
    }
}

/**
 * WordPress-specific dump functions with enhanced context
 */
if (!function_exists('dump_wp_context')) {
    function dump_wp_context() {
        global $wp, $wp_query, $post;
        
        $context = [
            'request' => [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                'uri' => $_SERVER['REQUEST_URI'] ?? 'Unknown',
                'query_string' => $_SERVER['QUERY_STRING'] ?? '',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            ],
            'wordpress' => [
                'current_action' => current_action(),
                'current_filter' => current_filter(),
                'doing_ajax' => wp_doing_ajax(),
                'doing_cron' => wp_doing_cron(),
                'is_rest_request' => defined('REST_REQUEST') && REST_REQUEST,
            ],
            'query_vars' => $wp_query->query_vars ?? [],
            'current_post' => $post ? [
                'ID' => $post->ID,
                'post_type' => $post->post_type,
                'post_status' => $post->post_status,
                'post_title' => $post->post_title,
            ] : null,
            'current_user' => wp_get_current_user()->ID ?: 'Not logged in',
            'memory_usage' => size_format(memory_get_usage(true)),
            'execution_time' => timer_stop(0, 3) . 's',
        ];
        
        dump($context);
    }
}

/**
 * Dump WordPress constants
 */
if (!function_exists('dump_wp_constants')) {
    function dump_wp_constants($pattern = null) {
        $constants = get_defined_constants(true)['user'];
        $wp_constants = [];
        
        foreach ($constants as $name => $value) {
            if (strpos($name, 'WP_') === 0 || strpos($name, 'ABSPATH') === 0 || 
                strpos($name, 'DB_') === 0 || strpos($name, 'AUTH_') === 0 ||
                strpos($name, 'SECURE_') === 0 || strpos($name, 'LOGGED_IN_') === 0) {
                
                if (!$pattern || stripos($name, $pattern) !== false) {
                    $wp_constants[$name] = $value;
                }
            }
        }
        
        ksort($wp_constants);
        dump($wp_constants);
    }
}
