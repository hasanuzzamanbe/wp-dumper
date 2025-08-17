<?php
/**
 * WordPress-Specific Debug Helper Functions
 * 
 * This file contains WordPress-specific debugging functions that extend
 * the basic dump() and dd() functionality with WordPress context.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dump WordPress query information
 */
if (!function_exists('dump_query')) {
    function dump_query($query = null) {
        global $wp_query, $wpdb;
        
        $query_info = [
            'main_query' => $query ?: $wp_query,
            'last_sql' => $wpdb->last_query,
            'query_count' => get_num_queries(),
            'query_time' => timer_stop(0, 3) . 's',
            'memory_usage' => size_format(memory_get_usage(true)),
            'peak_memory' => size_format(memory_get_peak_usage(true)),
        ];
        
        dump($query_info);
    }
}

/**
 * Dump current user information and capabilities
 */
if (!function_exists('dump_user')) {
    function dump_user($user_id = null) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        
        if (!$user || !$user->exists()) {
            dump(['error' => 'User not found or not logged in']);
            return;
        }
        
        $user_info = [
            'ID' => $user->ID,
            'login' => $user->user_login,
            'email' => $user->user_email,
            'display_name' => $user->display_name,
            'roles' => $user->roles,
            'capabilities' => array_keys(array_filter($user->allcaps)),
            'meta' => get_user_meta($user->ID),
        ];
        
        dump($user_info);
    }
}

/**
 * Dump WordPress hooks (actions and filters)
 */
if (!function_exists('dump_hooks')) {
    function dump_hooks($hook_name = null) {
        global $wp_filter;
        
        if ($hook_name) {
            $hooks = isset($wp_filter[$hook_name]) ? [$hook_name => $wp_filter[$hook_name]] : [];
        } else {
            $hooks = $wp_filter;
        }
        
        $formatted_hooks = [];
        foreach ($hooks as $name => $hook) {
            $formatted_hooks[$name] = [];
            foreach ($hook->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    $formatted_hooks[$name][$priority][] = [
                        'function' => wp_dumper_format_callback($callback['function']),
                        'accepted_args' => $callback['accepted_args'],
                    ];
                }
            }
        }
        
        dump($formatted_hooks);
    }
}

/**
 * Dump WordPress environment information
 */
if (!function_exists('dump_wp_env')) {
    function dump_wp_env() {
        global $wp_version;
        
        $env_info = [
            'wordpress' => [
                'version' => $wp_version,
                'multisite' => is_multisite(),
                'debug' => WP_DEBUG,
                'debug_log' => WP_DEBUG_LOG,
                'debug_display' => WP_DEBUG_DISPLAY,
                'script_debug' => SCRIPT_DEBUG,
            ],
            'server' => [
                'php_version' => PHP_VERSION,
                'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
                'memory_limit' => ini_get('memory_limit'),
                'max_execution_time' => ini_get('max_execution_time'),
                'upload_max_filesize' => ini_get('upload_max_filesize'),
                'post_max_size' => ini_get('post_max_size'),
            ],
            'theme' => [
                'active_theme' => get_template(),
                'child_theme' => get_stylesheet() !== get_template() ? get_stylesheet() : null,
                'theme_version' => wp_get_theme()->get('Version'),
            ],
            'plugins' => [
                'active_plugins' => get_option('active_plugins'),
                'mu_plugins' => get_mu_plugins(),
            ],
        ];
        
        dump($env_info);
    }
}

/**
 * Dump template hierarchy information
 */
if (!function_exists('dump_template')) {
    function dump_template() {
        global $template;
        
        $template_info = [
            'current_template' => $template,
            'template_hierarchy' => wp_dumper_get_template_hierarchy(),
            'is_functions' => [
                'is_home' => is_home(),
                'is_front_page' => is_front_page(),
                'is_single' => is_single(),
                'is_page' => is_page(),
                'is_archive' => is_archive(),
                'is_search' => is_search(),
                'is_404' => is_404(),
                'is_admin' => is_admin(),
                'is_ajax' => wp_doing_ajax(),
                'is_rest' => defined('REST_REQUEST') && REST_REQUEST,
            ],
            'queried_object' => get_queried_object(),
        ];
        
        dump($template_info);
    }
}

/**
 * Dump WordPress options
 */
if (!function_exists('dump_options')) {
    function dump_options($pattern = null) {
        global $wpdb;
        
        $where = $pattern ? $wpdb->prepare("WHERE option_name LIKE %s", '%' . $pattern . '%') : '';
        $options = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} {$where} ORDER BY option_name");
        
        $formatted_options = [];
        foreach ($options as $option) {
            $value = maybe_unserialize($option->option_value);
            $formatted_options[$option->option_name] = $value;
        }
        
        dump($formatted_options);
    }
}

/**
 * Dump WordPress transients
 */
if (!function_exists('dump_transients')) {
    function dump_transients($pattern = null) {
        global $wpdb;
        
        $where = $pattern 
            ? $wpdb->prepare("WHERE option_name LIKE %s", '%transient%' . $pattern . '%')
            : "WHERE option_name LIKE '%transient%'";
            
        $transients = $wpdb->get_results("SELECT option_name, option_value FROM {$wpdb->options} {$where} ORDER BY option_name");
        
        $formatted_transients = [];
        foreach ($transients as $transient) {
            $key = str_replace(['_transient_timeout_', '_transient_'], '', $transient->option_name);
            if (!isset($formatted_transients[$key])) {
                $formatted_transients[$key] = [];
            }
            
            if (strpos($transient->option_name, '_timeout_') !== false) {
                $formatted_transients[$key]['expires'] = date('Y-m-d H:i:s', $transient->option_value);
                $formatted_transients[$key]['expires_in'] = human_time_diff(time(), $transient->option_value);
            } else {
                $formatted_transients[$key]['value'] = maybe_unserialize($transient->option_value);
            }
        }
        
        dump($formatted_transients);
    }
}

/**
 * Dump WordPress performance metrics
 */
if (!function_exists('dump_performance')) {
    function dump_performance() {
        $performance = [
            'execution_time' => timer_stop(0, 3) . 's',
            'memory_usage' => [
                'current' => size_format(memory_get_usage(true)),
                'peak' => size_format(memory_get_peak_usage(true)),
                'limit' => ini_get('memory_limit'),
            ],
            'database' => [
                'queries' => get_num_queries(),
                'query_time' => function_exists('get_query_time') ? get_query_time() . 's' : 'N/A',
            ],
            'loaded_plugins' => count(get_option('active_plugins', [])),
            'loaded_mu_plugins' => count(get_mu_plugins()),
        ];
        
        dump($performance);
    }
}

/**
 * Helper function to format callback information
 */
function wp_dumper_format_callback($callback) {
    if (is_string($callback)) {
        return $callback;
    } elseif (is_array($callback)) {
        if (is_object($callback[0])) {
            return get_class($callback[0]) . '::' . $callback[1];
        } else {
            return $callback[0] . '::' . $callback[1];
        }
    } elseif (is_object($callback)) {
        if ($callback instanceof Closure) {
            return 'Closure';
        } else {
            return get_class($callback) . '::__invoke';
        }
    }
    return 'Unknown callback type';
}

/**
 * Helper function to get template hierarchy
 */
function wp_dumper_get_template_hierarchy() {
    $hierarchy = [];
    
    if (is_404()) {
        $hierarchy = ['404.php'];
    } elseif (is_search()) {
        $hierarchy = ['search.php', 'index.php'];
    } elseif (is_front_page()) {
        $hierarchy = ['front-page.php', 'home.php', 'index.php'];
    } elseif (is_home()) {
        $hierarchy = ['home.php', 'index.php'];
    } elseif (is_single()) {
        $post = get_queried_object();
        $hierarchy = [
            "single-{$post->post_type}-{$post->post_name}.php",
            "single-{$post->post_type}.php",
            'single.php',
            'singular.php',
            'index.php'
        ];
    } elseif (is_page()) {
        $page = get_queried_object();
        $hierarchy = [
            "page-{$page->post_name}.php",
            "page-{$page->ID}.php",
            'page.php',
            'singular.php',
            'index.php'
        ];
    }
    
    return $hierarchy;
}
