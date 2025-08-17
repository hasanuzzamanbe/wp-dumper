<?php
/**
 * WordPress Debug Toolkit
 * 
 * A comprehensive collection of WordPress-specific debugging functions
 * that extend the basic dump() and dd() functionality.
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Quick WordPress debugging functions
 */

// Dump current WordPress state
if (!function_exists('dump_wp')) {
    function dump_wp() {
        dump_wp_context();
    }
}

// Dump and die with WordPress context
if (!function_exists('ddwp')) {
    function ddwp(...$vars) {
        dump_wp_context();
        if (!empty($vars)) {
            dd(...$vars);
        } else {
            die();
        }
    }
}

// Quick post debugging
if (!function_exists('dump_post')) {
    function dump_post($post_id = null) {
        global $post;
        $target_post = $post_id ? get_post($post_id) : $post;
        
        if (!$target_post) {
            dump(['error' => 'Post not found']);
            return;
        }
        
        $post_data = [
            'post' => $target_post,
            'meta' => get_post_meta($target_post->ID),
            'terms' => wp_get_post_terms($target_post->ID, get_object_taxonomies($target_post->post_type)),
            'author' => get_user_by('id', $target_post->post_author),
        ];
        
        dump($post_data);
    }
}

// Quick WP_Query debugging
if (!function_exists('dump_wp_query')) {
    function dump_wp_query($query = null) {
        global $wp_query;
        $target_query = $query ?: $wp_query;
        
        $query_data = [
            'query_vars' => $target_query->query_vars,
            'request' => $target_query->request,
            'found_posts' => $target_query->found_posts,
            'max_num_pages' => $target_query->max_num_pages,
            'is_functions' => [
                'is_main_query' => $target_query->is_main_query(),
                'is_home' => $target_query->is_home(),
                'is_single' => $target_query->is_single(),
                'is_page' => $target_query->is_page(),
                'is_archive' => $target_query->is_archive(),
            ],
            'queried_object' => $target_query->get_queried_object(),
        ];
        
        dump($query_data);
    }
}

// Debug WordPress rewrite rules
if (!function_exists('dump_rewrites')) {
    function dump_rewrites($pattern = null) {
        global $wp_rewrite;
        
        $rules = get_option('rewrite_rules');
        if ($pattern) {
            $rules = array_filter($rules, function($key) use ($pattern) {
                return stripos($key, $pattern) !== false;
            }, ARRAY_FILTER_USE_KEY);
        }
        
        $rewrite_data = [
            'rules' => $rules,
            'permalink_structure' => get_option('permalink_structure'),
            'rewrite_rules_count' => count(get_option('rewrite_rules', [])),
            'wp_rewrite' => [
                'permalink_structure' => $wp_rewrite->permalink_structure,
                'use_verbose_page_rules' => $wp_rewrite->use_verbose_page_rules,
                'use_verbose_rules' => $wp_rewrite->use_verbose_rules,
            ],
        ];
        
        dump($rewrite_data);
    }
}

// Debug WordPress cron
if (!function_exists('dump_cron')) {
    function dump_cron() {
        $cron_jobs = get_option('cron');
        $schedules = wp_get_schedules();
        
        $formatted_jobs = [];
        foreach ($cron_jobs as $timestamp => $jobs) {
            foreach ($jobs as $hook => $job_data) {
                foreach ($job_data as $key => $job) {
                    $formatted_jobs[] = [
                        'hook' => $hook,
                        'next_run' => date('Y-m-d H:i:s', $timestamp),
                        'next_run_in' => human_time_diff(time(), $timestamp),
                        'schedule' => $job['schedule'] ?? 'single',
                        'args' => $job['args'] ?? [],
                    ];
                }
            }
        }
        
        usort($formatted_jobs, function($a, $b) {
            return strtotime($a['next_run']) - strtotime($b['next_run']);
        });
        
        $cron_data = [
            'scheduled_jobs' => array_slice($formatted_jobs, 0, 20),
            'total_jobs' => count($formatted_jobs),
            'available_schedules' => $schedules,
            'doing_cron' => wp_doing_cron(),
            'cron_disabled' => defined('DISABLE_WP_CRON') && DISABLE_WP_CRON,
        ];
        
        dump($cron_data);
    }
}

// Debug WordPress menus
if (!function_exists('dump_menus')) {
    function dump_menus($location = null) {
        $menus = wp_get_nav_menus();
        $locations = get_nav_menu_locations();
        
        $menu_data = [
            'registered_locations' => get_registered_nav_menus(),
            'assigned_locations' => $locations,
            'available_menus' => [],
        ];
        
        foreach ($menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            $menu_data['available_menus'][$menu->name] = [
                'id' => $menu->term_id,
                'slug' => $menu->slug,
                'count' => $menu->count,
                'items' => array_map(function($item) {
                    return [
                        'title' => $item->title,
                        'url' => $item->url,
                        'type' => $item->type,
                        'object' => $item->object,
                        'parent' => $item->menu_item_parent,
                    ];
                }, $menu_items ?: []),
            ];
        }
        
        if ($location && isset($locations[$location])) {
            $menu_data = array_filter($menu_data['available_menus'], function($menu) use ($locations, $location) {
                return $menu['id'] == $locations[$location];
            });
        }
        
        dump($menu_data);
    }
}

// Debug WordPress widgets
if (!function_exists('dump_widgets')) {
    function dump_widgets($sidebar = null) {
        global $wp_registered_widgets, $wp_registered_sidebars;
        
        $sidebars_widgets = wp_get_sidebars_widgets();
        
        $widget_data = [
            'registered_sidebars' => $wp_registered_sidebars,
            'active_widgets' => $sidebars_widgets,
            'registered_widgets' => array_keys($wp_registered_widgets),
        ];
        
        if ($sidebar && isset($sidebars_widgets[$sidebar])) {
            $widget_data = [
                'sidebar' => $sidebar,
                'widgets' => $sidebars_widgets[$sidebar],
                'sidebar_info' => $wp_registered_sidebars[$sidebar] ?? null,
            ];
        }
        
        dump($widget_data);
    }
}

// Debug WordPress REST API
if (!function_exists('dump_rest')) {
    function dump_rest() {
        global $wp_rest_server;
        
        $rest_data = [
            'is_rest_request' => defined('REST_REQUEST') && REST_REQUEST,
            'rest_url' => get_rest_url(),
            'rest_enabled' => !empty($wp_rest_server),
            'current_user_can_rest' => current_user_can('edit_posts'),
        ];
        
        if (!empty($wp_rest_server)) {
            $routes = $wp_rest_server->get_routes();
            $rest_data['total_routes'] = count($routes);
            $rest_data['sample_routes'] = array_slice(array_keys($routes), 0, 10);
        }
        
        dump($rest_data);
    }
}

// Debug WordPress capabilities
if (!function_exists('dump_caps')) {
    function dump_caps($user_id = null) {
        $user = $user_id ? get_user_by('id', $user_id) : wp_get_current_user();
        
        if (!$user || !$user->exists()) {
            dump(['error' => 'User not found']);
            return;
        }
        
        $caps_data = [
            'user' => [
                'ID' => $user->ID,
                'login' => $user->user_login,
                'roles' => $user->roles,
            ],
            'capabilities' => $user->allcaps,
            'role_capabilities' => [],
        ];
        
        foreach ($user->roles as $role_name) {
            $role = get_role($role_name);
            if ($role) {
                $caps_data['role_capabilities'][$role_name] = $role->capabilities;
            }
        }
        
        dump($caps_data);
    }
}

/**
 * WordPress debugging shortcuts
 */

// Quick performance check
if (!function_exists('perf')) {
    function perf() {
        dump_performance();
    }
}

// Quick query check
if (!function_exists('queries')) {
    function queries() {
        dump_query();
    }
}

// Quick environment check
if (!function_exists('env')) {
    function env() {
        dump_wp_env();
    }
}

/**
 * REST API specific debugging functions
 */

// Dump REST API request information
if (!function_exists('dump_rest_request')) {
    function dump_rest_request() {
        if (!defined('REST_REQUEST') || !REST_REQUEST) {
            dump(['error' => 'Not a REST API request']);
            return;
        }

        global $wp;

        $request_data = [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
            'route' => $wp->query_vars['rest_route'] ?? 'Unknown',
            'params' => $_GET,
            'body' => file_get_contents('php://input'),
            'headers' => getallheaders(),
            'user' => wp_get_current_user()->ID ?: 'Not authenticated',
            'nonce_valid' => wp_verify_nonce($_REQUEST['_wpnonce'] ?? '', 'wp_rest') ? 'Valid' : 'Invalid/Missing',
        ];

        // Try to decode JSON body
        if (!empty($request_data['body'])) {
            $decoded = json_decode($request_data['body'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $request_data['body_decoded'] = $decoded;
            }
        }

        dump($request_data);
    }
}

// Dump current REST API route information
if (!function_exists('dump_rest_route')) {
    function dump_rest_route() {
        global $wp_rest_server;

        if (!$wp_rest_server) {
            dump(['error' => 'REST server not initialized']);
            return;
        }

        $routes = $wp_rest_server->get_routes();
        $current_route = $_SERVER['REQUEST_URI'] ?? '';

        // Find matching routes
        $matching_routes = [];
        foreach ($routes as $route => $handlers) {
            if (preg_match('#' . $route . '#', $current_route)) {
                $matching_routes[$route] = $handlers;
            }
        }

        $route_data = [
            'current_request' => $current_route,
            'matching_routes' => $matching_routes,
            'total_routes' => count($routes),
            'sample_routes' => array_slice(array_keys($routes), 0, 10),
        ];

        dump($route_data);
    }
}

// Enhanced REST debugging that works with the new JSON output
if (!function_exists('dump_rest')) {
    function dump_rest() {
        global $wp_rest_server;

        $rest_data = [
            'is_rest_request' => defined('REST_REQUEST') && REST_REQUEST,
            'rest_url' => get_rest_url(),
            'rest_enabled' => !empty($wp_rest_server),
            'current_user_can_rest' => current_user_can('edit_posts'),
            'request_info' => defined('REST_REQUEST') && REST_REQUEST ? [
                'method' => $_SERVER['REQUEST_METHOD'] ?? 'Unknown',
                'route' => $_GET['rest_route'] ?? 'Unknown',
                'authenticated' => is_user_logged_in(),
            ] : null,
        ];

        if (!empty($wp_rest_server)) {
            $routes = $wp_rest_server->get_routes();
            $rest_data['total_routes'] = count($routes);
            $rest_data['sample_routes'] = array_slice(array_keys($routes), 0, 10);
        }

        dump($rest_data);
    }
}
