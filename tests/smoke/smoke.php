<?php
/**
 * Grav 2 smoke test for the Webp plugin.
 *
 * Unlike the unit tests, this boots a real Grav install and exercises the plugin
 * as it is shipped (vendor/ included), so it catches breakage in plugin
 * initialization, Twig filter registration and the CLI commands.
 *
 * The plugin must be installed in <grav>/user/plugins/webp and enabled.
 *
 * Usage:
 *   cd <grav> && php user/plugins/webp/tests/smoke/smoke.php
 *   php tests/smoke/smoke.php <grav>
 *
 * It creates one temporary image (user/images/webp-smoke-test.jpg), converts it,
 * removes the result and cleans up after itself. Exit code is 0 when every check
 * passes and 1 otherwise.
 *
 * @author Greenrivers
 * @package Grav\Plugin\Webp
 */

use Grav\Common\Grav;
use Twig\TwigFilter;

const FIXTURE_NAME = 'webp-smoke-test';

$failures = 0;

/**
 * @param bool $condition
 * @param string $label
 * @param string $details
 * @return bool
 */
function check(bool $condition, string $label, string $details = ''): bool
{
    global $failures;

    if ($condition) {
        echo "ok   - $label\n";
    } else {
        $failures++;
        echo "FAIL - $label" . ($details !== '' ? "\n       " . str_replace("\n", "\n       ", trim($details)) : '') . "\n";
    }

    return $condition;
}

/**
 * Runs one of the plugin's CLI commands the same way a user would.
 *
 * @param string ...$arguments
 * @return array{0: int, 1: string} exit code and combined output
 */
function runPluginCommand(string ...$arguments): array
{
    $command = array_merge([PHP_BINARY, 'bin/plugin', 'webp'], $arguments, ['--no-ansi']);
    $process = proc_open($command, [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

    if (!is_resource($process)) {
        return [-1, 'Unable to start: ' . implode(' ', $command)];
    }

    $output = stream_get_contents($pipes[1]) . stream_get_contents($pipes[2]);
    fclose($pipes[1]);
    fclose($pipes[2]);

    return [proc_close($process), $output];
}

/**
 * @param string $directory
 * @return void
 */
function removeDirectoryIfEmpty(string $directory): void
{
    if (is_dir($directory) && count(scandir($directory)) === 2) {
        rmdir($directory);
    }
}

$root = $argv[1] ?? getcwd();

if (!is_file($root . '/bin/plugin') || !is_file($root . '/user/plugins/webp/webp.php')) {
    fwrite(STDERR, "Not a Grav install with the webp plugin in user/plugins/webp: $root\n");
    fwrite(STDERR, "Usage: php tests/smoke/smoke.php <path-to-grav>\n");
    exit(2);
}

// Grav derives GRAV_ROOT from the working directory and the plugin uses root-relative paths.
chdir($root);

// Bootstrap the same way bin/plugin does.
define('GRAV_CLI', true);
define('GRAV_REQUEST_TIME', microtime(true));

$autoload = require 'vendor/autoload.php';

$grav = Grav::instance(['loader' => $autoload]);
$grav->initializeCli();
$grav['plugins']->init();
$grav['themes']->init();
$grav->fireEvent('onPluginsInitialized');

echo 'Grav ' . GRAV_VERSION . ', PHP ' . PHP_VERSION . "\n\n";

check(version_compare(GRAV_VERSION, '2.0.0', '>='), 'runs on Grav 2 or newer', 'Found Grav ' . GRAV_VERSION);

if (!check((bool)$grav['config']->get('plugins.webp.enabled'), 'webp plugin is enabled')) {
    exit(1);
}

// 1. Twig filter registration.
$grav['twig']->init();
$twig = $grav['twig']->twig();

foreach (['webp', 'extension'] as $filterName) {
    check($twig->getFilter($filterName) instanceof TwigFilter, "Twig filter \"$filterName\" is registered");
}

$imagesDirCreated = !is_dir('user/images') && mkdir('user/images', 0775, true);
$webpDirExisted = is_dir('user/webp');

$imagePath = 'user/images/' . FIXTURE_NAME . '.jpg';
$imageUrl = '/' . $imagePath;
// Default location (original_path: false) and the location used when original_path is enabled.
$webpPaths = ['user/webp/user/images/' . FIXTURE_NAME . '.webp', 'user/images/' . FIXTURE_NAME . '.webp'];
// The CLI resolves --path against the Grav root using the platform's directory separator.
$cliPath = static function (string $path): string {
    return str_replace('/', DIRECTORY_SEPARATOR, $path);
};
$render = static function (string $template) use ($twig, $imageUrl): string {
    return $twig->createTemplate($template)->render(['image' => $imageUrl]);
};

try {
    $fixture = imagecreatetruecolor(16, 16);
    imagefill($fixture, 0, 0, imagecolorallocate($fixture, 60, 120, 180));
    imagejpeg($fixture, $imagePath);

    check($render('{{ image|extension }}') === 'jpg', 'Twig filter "extension" returns the image extension');
    check(
        $render('{{ image|webp }}') === $imageUrl,
        'Twig filter "webp" leaves the path alone while no webp file exists'
    );

    // 2. CLI: convert a single image.
    [, $output] = runPluginCommand('convert', '--path=' . $cliPath($imagePath));
    $webpPath = current(array_filter($webpPaths, 'is_file'));

    if (check($webpPath !== false, 'CLI "convert --path" creates the webp file', $output)) {
        check(exif_imagetype($webpPath) === IMAGETYPE_WEBP, 'converted file is a valid webp image');

        if (DIRECTORY_SEPARATOR === '/') {
            $webpUrl = $render('{{ image|webp }}');
            check(
                $webpUrl !== $imageUrl && str_ends_with($webpUrl, FIXTURE_NAME . '.webp') && is_file($root . $webpUrl),
                'Twig filter "webp" returns the converted image',
                "Rendered: $webpUrl"
            );
        } else {
            echo "skip - Twig filter \"webp\" returns the converted image (the filter splits URLs on the directory\n"
                . "       separator, so it only resolves webp files on platforms where that is \"/\")\n";
        }

        // 3. CLI: remove it again.
        [, $output] = runPluginCommand('clear', '--path=' . $cliPath($webpPath));
        clearstatcache();
        check(!is_file($webpPath), 'CLI "clear --path" removes the webp file', $output);
    }
} catch (Throwable $e) {
    check(false, 'smoke test ran to completion', get_class($e) . ': ' . $e->getMessage() . "\n" . $e->getTraceAsString());
} finally {
    foreach (array_merge([$imagePath], $webpPaths) as $path) {
        if (is_file($path)) {
            unlink($path);
        }
    }

    if (!$webpDirExisted) {
        removeDirectoryIfEmpty('user/webp/user/images');
        removeDirectoryIfEmpty('user/webp/user');
        removeDirectoryIfEmpty('user/webp');
    }

    if ($imagesDirCreated) {
        removeDirectoryIfEmpty('user/images');
    }
}

echo "\n" . ($failures === 0 ? 'Smoke test passed.' : "Smoke test failed: $failures check(s).") . "\n";

exit($failures === 0 ? 0 : 1);
