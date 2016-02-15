#!/usr/bin/env php
<?php
/**
 * demo console
 */

use Commands\ImportPalsCommand,
    Commands\CreateDBCommand;

set_time_limit(0);

$app = require_once __DIR__ . '/bootstrap.php';

$console = &$app['console'];
// add commands with $console->add(<command name>);
$console->add(new ImportPalsCommand());
$console->add(new CreateDBCommand());
$console->run();
