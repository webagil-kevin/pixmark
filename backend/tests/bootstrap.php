<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

require dirname(__DIR__).'/vendor/autoload.php';

// Ensure we're in test environment
$_SERVER['APP_ENV'] = 'test';
$_ENV['APP_ENV'] = 'test';

if (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/.env');
}

// Ensure var directory exists and is clean
$varDir = dirname(__DIR__).'/var';
$testDbFile = $varDir.'/test.db';
$fs = new Filesystem();

// Create var directory if it doesn't exist
if (!$fs->exists($varDir)) {
    $fs->mkdir($varDir);
}

// Remove existing test database
if ($fs->exists($testDbFile)) {
    $fs->remove($testDbFile);
}

// Create empty SQLite database file
touch($testDbFile);

// Boot the kernel
$kernel = new App\Kernel('test', true);
$kernel->boot();

// Create the schema
$application = new Application($kernel);
$application->setAutoExit(false);

$schemaInput = new ArrayInput([
    'command' => 'doctrine:schema:create',
    '--env' => 'test',
]);
$application->run($schemaInput, new NullOutput());