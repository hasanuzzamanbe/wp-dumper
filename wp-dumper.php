<?php
/**
 * Plugin Name:       WP Dumper
 * Plugin URI:        https://wpminers.com/
 * Description:       Provides Laravel-style variable dumping to a web-based viewer.
 * Version:           2.2.0
 * Author:            Hasanuzzaman
 * Author URI:        https://hasanuzzaman.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       wp-dumper
 */

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define the plugin path for easier access.
define('WP_LARAVEL_DUMPER_PATH', plugin_dir_path(__FILE__));

// Global array to hold buffered dumps for later output.
$GLOBALS['wp_dumper_vars_to_dump'] = [];

// Include the Composer autoloader.
if (file_exists(WP_LARAVEL_DUMPER_PATH . 'vendor/autoload.php')) {
    require_once WP_LARAVEL_DUMPER_PATH . 'vendor/autoload.php';
} else {
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>';
        echo esc_html__('WP Laravel Dumper: The Composer autoloader is missing. Please run "composer install" in the plugin directory.', 'wp_dumper');
        echo '</p></div>';
    });
    return;
}

// Include WordPress-specific debugging helpers
require_once WP_LARAVEL_DUMPER_PATH . 'dumpers/wp-debug-helpers.php';
require_once WP_LARAVEL_DUMPER_PATH . 'dumpers/wp-debug-profiler.php';
require_once WP_LARAVEL_DUMPER_PATH . 'dumpers/wp-query-analyzer.php';
require_once WP_LARAVEL_DUMPER_PATH . 'dumpers/wp-debug-toolkit.php';

/**
 * Configure the VarDumper handler.
 *
 * This handler now decides whether to send dumps to the server OR
 * buffer them to be printed safely in the footer. It never outputs directly.
 */
VarDumper::setHandler(function ($var) {
    // We send to the server when WP_DEBUG is on and we are not in the admin area.
    $useServer = defined('WP_DEBUG') && WP_DEBUG && !is_admin();

    $cloner = new VarCloner();
    $dumper = new HtmlDumper();

    // Capture the file and line number where the dump was called.
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
    $source = [
        'file' => $backtrace[3]['file'] ?? 'unknown',
        'line' => $backtrace[3]['line'] ?? 'unknown',
    ];

    // Use output buffering to capture the HTML output of the dumper as a string.
    // This is the key to preventing the "headers already sent" error.
    ob_start();
    $dumper->dump($cloner->cloneVar($var));
    $htmlOutput = ob_get_clean();

    if ($useServer) {
        // The URL for our local Node.js dump server.
        $serverUrl = 'http://localhost:9913/dump';

        // Send the captured HTML to the server.
        wp_remote_post($serverUrl, [
            'headers' => ['Content-Type' => 'application/json'],
            'body'    => json_encode([
                'html'   => $htmlOutput,
                'source' => $source,
                'time'   => date('H:i:s'),
            ]),
            'blocking' => false, // Set to false to not slow down the PHP request.
            'timeout'  => 2,
        ]);

    } else {
        // Fallback: Buffer the dump to print in the footer.
        $GLOBALS['wp_dumper_vars_to_dump'][] = $htmlOutput;
    }
});

/**
 * Outputs the buffered dump variables in the footer.
 * This function is hooked into `wp_footer` and `admin_footer`.
 */
function wp_dumper_output_vars() {
    if (empty($GLOBALS['wp_dumper_vars_to_dump'])) {
        return;
    }

    // Basic wrapper for styling and clarity
    echo '<div class="wp-dumper-output-wrapper" style="padding: 20px; background: #222; margin-top: 30px; border-top: 5px solid #5864f2; z-index: 99999; position: relative;">';
    foreach ($GLOBALS['wp_dumper_vars_to_dump'] as $dumpHtml) {
        echo $dumpHtml;
    }
    echo '</div>';
}
add_action('wp_footer', 'wp_dumper_output_vars', 999);
add_action('admin_footer', 'wp_dumper_output_vars', 999);


if (!function_exists('dump')) {
    /**
     * Dumps information about one or more variables.
     */
    function dump(...$vars)
    {
        foreach ($vars as $var) {
            VarDumper::dump($var);
        }
    }
}

//if (!function_exists('ddd')) {
    /**
     * Dumps the given variables and ends the script.
     */
    function dd(...$vars)
{
    // Check if this is a REST API or AJAX request
    $isRestRequest = defined('REST_REQUEST') && REST_REQUEST;
    $isAjaxRequest = wp_doing_ajax();

    if ($isRestRequest || $isAjaxRequest) {
        // For AJAX, output HTML directly
        echo '<pre>';
        foreach ($vars as $var) {
            var_dump($var);
        }
        echo '</pre>';
        die();
    } else {
        // For regular requests, use the original behavior
        echo '<div style="padding: 10px; background: #18171B;">';

        // Temporarily disable the custom handler to dump directly.
        $handler = VarDumper::setHandler(null);

        foreach ($vars as $var) {
            VarDumper::dump($var);
        }

        // Restore the original handler if it existed.
        VarDumper::setHandler($handler);

        echo '</div>';
        die(1);
    }
}
//}

