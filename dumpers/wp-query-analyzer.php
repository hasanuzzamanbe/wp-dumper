<?php
/**
 * WordPress Query Analyzer
 * 
 * Advanced database query analysis and debugging tools
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class WP_Query_Analyzer {
    private static $query_log = [];
    private static $is_logging = false;

    /**
     * Start query logging
     */
    public static function start_logging() {
        if (!self::$is_logging) {
            self::$is_logging = true;
            add_filter('query', [__CLASS__, 'log_query']);
            add_action('shutdown', [__CLASS__, 'analyze_queries']);
        }
    }

    /**
     * Stop query logging
     */
    public static function stop_logging() {
        self::$is_logging = false;
        remove_filter('query', [__CLASS__, 'log_query']);
    }

    /**
     * Log individual queries
     */
    public static function log_query($query) {
        if (self::$is_logging) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
            
            self::$query_log[] = [
                'query' => $query,
                'time' => microtime(true),
                'backtrace' => self::format_backtrace($backtrace),
                'type' => self::get_query_type($query),
                'tables' => self::extract_tables($query),
            ];
        }
        return $query;
    }

    /**
     * Analyze all logged queries
     */
    public static function analyze_queries() {
        if (empty(self::$query_log)) {
            return;
        }

        $analysis = [
            'summary' => self::get_query_summary(),
            'by_type' => self::group_by_type(),
            'by_table' => self::group_by_table(),
            'duplicate_queries' => self::find_duplicate_queries(),
            'slow_queries' => self::find_potential_slow_queries(),
            'n_plus_one' => self::detect_n_plus_one(),
        ];

        dump($analysis);
    }

    /**
     * Get query summary statistics
     */
    private static function get_query_summary() {
        $total = count(self::$query_log);
        $types = array_count_values(array_column(self::$query_log, 'type'));
        
        return [
            'total_queries' => $total,
            'by_type' => $types,
            'unique_queries' => count(array_unique(array_column(self::$query_log, 'query'))),
            'duplicate_count' => $total - count(array_unique(array_column(self::$query_log, 'query'))),
        ];
    }

    /**
     * Group queries by type
     */
    private static function group_by_type() {
        $grouped = [];
        foreach (self::$query_log as $query_data) {
            $type = $query_data['type'];
            if (!isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = substr($query_data['query'], 0, 100) . '...';
        }
        return $grouped;
    }

    /**
     * Group queries by table
     */
    private static function group_by_table() {
        $grouped = [];
        foreach (self::$query_log as $query_data) {
            foreach ($query_data['tables'] as $table) {
                if (!isset($grouped[$table])) {
                    $grouped[$table] = 0;
                }
                $grouped[$table]++;
            }
        }
        arsort($grouped);
        return $grouped;
    }

    /**
     * Find duplicate queries
     */
    private static function find_duplicate_queries() {
        $query_counts = array_count_values(array_column(self::$query_log, 'query'));
        $duplicates = array_filter($query_counts, function($count) {
            return $count > 1;
        });
        
        arsort($duplicates);
        $result = [];
        foreach (array_slice($duplicates, 0, 10, true) as $query => $count) {
            $result[] = [
                'query' => substr($query, 0, 150) . '...',
                'count' => $count
            ];
        }
        return $result;
    }

    /**
     * Find potentially slow queries
     */
    private static function find_potential_slow_queries() {
        $slow_queries = [];
        
        foreach (self::$query_log as $query_data) {
            $query = $query_data['query'];
            $score = 0;
            
            // Check for potential performance issues
            if (stripos($query, 'SELECT *') !== false) $score += 2;
            if (stripos($query, 'ORDER BY') !== false && stripos($query, 'LIMIT') === false) $score += 3;
            if (stripos($query, 'LIKE') !== false) $score += 2;
            if (stripos($query, 'NOT IN') !== false) $score += 3;
            if (preg_match('/JOIN.*JOIN/i', $query)) $score += 2;
            if (strlen($query) > 1000) $score += 2;
            
            if ($score >= 4) {
                $slow_queries[] = [
                    'query' => substr($query, 0, 200) . '...',
                    'score' => $score,
                    'issues' => self::identify_query_issues($query),
                    'backtrace' => $query_data['backtrace'],
                ];
            }
        }
        
        usort($slow_queries, function($a, $b) {
            return $b['score'] - $a['score'];
        });
        
        return array_slice($slow_queries, 0, 5);
    }

    /**
     * Detect N+1 query problems
     */
    private static function detect_n_plus_one() {
        $patterns = [];
        
        foreach (self::$query_log as $query_data) {
            $query = $query_data['query'];
            $normalized = preg_replace('/\b\d+\b/', 'N', $query);
            $normalized = preg_replace('/IN \([^)]+\)/', 'IN (...)', $normalized);
            
            if (!isset($patterns[$normalized])) {
                $patterns[$normalized] = [];
            }
            $patterns[$normalized][] = $query_data;
        }
        
        $n_plus_one = [];
        foreach ($patterns as $pattern => $queries) {
            if (count($queries) > 5) {
                $n_plus_one[] = [
                    'pattern' => substr($pattern, 0, 200) . '...',
                    'count' => count($queries),
                    'sample_backtrace' => $queries[0]['backtrace'],
                ];
            }
        }
        
        return $n_plus_one;
    }

    /**
     * Identify specific issues in a query
     */
    private static function identify_query_issues($query) {
        $issues = [];
        
        if (stripos($query, 'SELECT *') !== false) {
            $issues[] = 'Using SELECT * instead of specific columns';
        }
        if (stripos($query, 'ORDER BY') !== false && stripos($query, 'LIMIT') === false) {
            $issues[] = 'ORDER BY without LIMIT can be expensive';
        }
        if (stripos($query, 'LIKE') !== false) {
            $issues[] = 'LIKE queries can be slow without proper indexing';
        }
        if (stripos($query, 'NOT IN') !== false) {
            $issues[] = 'NOT IN can be slow, consider NOT EXISTS';
        }
        
        return $issues;
    }

    /**
     * Get query type
     */
    private static function get_query_type($query) {
        $query = trim(strtoupper($query));
        if (strpos($query, 'SELECT') === 0) return 'SELECT';
        if (strpos($query, 'INSERT') === 0) return 'INSERT';
        if (strpos($query, 'UPDATE') === 0) return 'UPDATE';
        if (strpos($query, 'DELETE') === 0) return 'DELETE';
        return 'OTHER';
    }

    /**
     * Extract table names from query
     */
    private static function extract_tables($query) {
        global $wpdb;
        
        $tables = [];
        $wp_tables = [
            $wpdb->posts, $wpdb->postmeta, $wpdb->users, $wpdb->usermeta,
            $wpdb->comments, $wpdb->commentmeta, $wpdb->terms, $wpdb->term_taxonomy,
            $wpdb->term_relationships, $wpdb->options
        ];
        
        foreach ($wp_tables as $table) {
            if (stripos($query, $table) !== false) {
                $tables[] = str_replace($wpdb->prefix, 'wp_', $table);
            }
        }
        
        return array_unique($tables);
    }

    /**
     * Format backtrace for display
     */
    private static function format_backtrace($backtrace) {
        $formatted = [];
        
        foreach ($backtrace as $trace) {
            if (isset($trace['file']) && isset($trace['line'])) {
                $file = str_replace(ABSPATH, '', $trace['file']);
                $function = isset($trace['class']) 
                    ? $trace['class'] . $trace['type'] . $trace['function']
                    : $trace['function'];
                    
                $formatted[] = $function . ' (' . $file . ':' . $trace['line'] . ')';
                
                if (count($formatted) >= 3) break;
            }
        }
        
        return $formatted;
    }
}

/**
 * Global functions for query analysis
 */
if (!function_exists('start_query_analysis')) {
    function start_query_analysis() {
        WP_Query_Analyzer::start_logging();
    }
}

if (!function_exists('stop_query_analysis')) {
    function stop_query_analysis() {
        WP_Query_Analyzer::stop_logging();
    }
}

if (!function_exists('dump_query_analysis')) {
    function dump_query_analysis() {
        WP_Query_Analyzer::analyze_queries();
    }
}
