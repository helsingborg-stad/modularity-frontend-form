#!/bin/php
<?php
declare(strict_types=1);
// Only allow run from cli.
if (php_sapi_name() !== 'cli') {
    exit(0);
}

/* Parameters: 
 --no-composer      Does not install vendors via composer
 --cleanup          Remove removeables
 --install-npm      Installs npm package as per package.json name field
 --release          Does not run composer install and does not remove .git
*/

// Any command needed to run and build plugin assets when newly cheched out of repo.
$buildCommands = [];

//Add composer build, if flag --no-composer is undefined.
//Dump autloader. 
//Only if composer.json exists.
if (file_exists('composer.json')) {
    if (is_array($argv) && !in_array('--no-composer', $argv, true)) {
        $buildCommands[] = 'composer install --prefer-dist --no-progress --no-dev';
    }

    // Colocated tests are not part of the production artifact.
    if (in_array('--cleanup', $argv, true)) {
        $buildCommands[] = "find source/php -type f \\( -name '*Test.php' -o -name '*.test.php' \\) -delete";
        // Service archives include tests; *Test.php also names real service contracts.
        $buildCommands[] = "find vendor -type f -name '*.test.php' -delete";
        $buildCommands[] = 'find vendor -mindepth 3 -maxdepth 3 -type d -name tests -exec rm -rf -- {} +';
    }
    $buildCommands[] = 'composer dump-autoload --no-dev';
}

//Run npm if package.json is found
if (file_exists('package.json') && file_exists('package-lock.json')) {
    if (is_array($argv) && !in_array('--install-npm', $argv, true)) {
        $buildCommands[] = 'npm ci --no-progress --no-audit';
        $buildCommands[] = 'npm run build';
    } else {
        $npmPackage = json_decode(file_get_contents('package.json'));
        $buildCommands[] = "npm install $npmPackage->name";
        $buildCommands[] = "rm -rf ./dist";
        $buildCommands[] = "mv node_modules/$npmPackage->name/dist ./";
    }
} elseif (file_exists('package.json') && !file_exists('package-lock.json')) {
    if (is_array($argv) && !in_array('--install-npm', $argv, true)) {
        $buildCommands[] = 'npm install --no-progress --no-audit';
        $buildCommands[] = 'npm run build';
    } else {
        $npmPackage = json_decode(file_get_contents('package.json'));
        $buildCommands[] = "npm install $npmPackage->name";
        $buildCommands[] = "rm -rf ./dist";
        $buildCommands[] = "mv node_modules/$npmPackage->name/dist ./";
    }
}

// Files and directories not suitable for prod to be removed.
$removables = [
    '.gitignore',
    '.github',
    '.gitattributes',
    'build.php',
    'build.js',
    '.npmrc',
    //'composer.json',
    'composer.lock',
    'env-example',
    'webpack.config.js',
    'package-lock.json',
    'package.json',
    'phpunit.xml.dist',
    'phpunit.xml',
    'bootstrap.php',
    'jest.config.js',
    'patchwork.json',
    'source/tests',
    '.playwright-cli',
    'README.md',
    './node_modules/',
    './source/sass/',
    './source/js/',
    'LICENSE',
    'babel.config.js',
    'yarn.lock',
    '.devcontainer',
    'vite.config.mjs',
    'tsconfig.json',
    'mago.toml',
    '.vscode',
    'phpunit-log.xml'
];

if (is_array($argv) && !in_array('--release', $argv, true)) {
    $removables = array_merge($removables, ['.git']);
}

$dirName = basename(dirname(__FILE__));

// Run all build commands.
$output = '';
$exitCode = 0;
foreach ($buildCommands as $buildCommand) {
    print "---- Running build command '$buildCommand' for $dirName. ----\n";
    $timeStart = microtime(true);
    $exitCode = executeCommand($buildCommand);
    $buildTime = round(microtime(true) - $timeStart);
    print "---- Done build command '$buildCommand' for $dirName.  Build time: $buildTime seconds. ----\n\n";
    if ($exitCode > 0) {
        exit($exitCode);
    }
}

// Remove files and directories if '--cleanup' argument is supplied to save local developers from disasters.
if (is_array($argv) && in_array('--cleanup', $argv, true)) {
    $removables = array_merge($removables, glob('vendor/*/*/.devcontainer'), glob('vendor/*/*/.github'), glob('vendor/*/*/phpunit*.xml*'));
    foreach ($removables as $removable) {
        if (file_exists($removable)) {
            print "Removing $removable from $dirName\n";
            $exitCode = executeCommand('rm -rf -- ' . escapeshellarg($removable));
            if ($exitCode !== 0) {
                exit($exitCode);
            }
        }
    }
}

/**
 * Better shell script execution with live output to STDOUT and status code return.
 * @param  string $command Command to execute in shell.
 * @return int             Exit code.
 */
function executeCommand($command)
{
    $fullCommand = '';
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $fullCommand = "cmd /v:on /c \"$command 2>&1 & echo Exit status : !ErrorLevel!\"";
    } else {
        $fullCommand = "$command 2>&1 ; echo Exit status : $?";
    }

    $proc = popen($fullCommand, 'r');

    $liveOutput     = '';
    $completeOutput = '';

    while (!feof($proc)) {
        $liveOutput     = fread($proc, 4096);
        $completeOutput = $completeOutput . $liveOutput;
        print $liveOutput;
        flush();
    }

    pclose($proc);

    // Get exit status.
    preg_match('/[0-9]+$/', $completeOutput, $matches);

    // Return exit status.
    return intval($matches[0]);
}
