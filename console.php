#!/usr/bin/env php
<?php
/**
 * demo console
 */

use Commands\ImportPalsCommand;
use Commands\CreateDBCommand;
use Commands\ImportOpenColorCommand;

set_time_limit(0);

$app = require_once __DIR__.'/bootstrap.php';

$console = &$app['console'];
// add commands with $console->add(<command name>);
$console->add(new ImportPalsCommand());
$console->add(new CreateDBCommand());
$console->add(new ImportOpenColorCommand());
$console->run();
