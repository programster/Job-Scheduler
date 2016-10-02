<?php

/*
 * Define the settings for this application.
 */

global $globals;
$globals = array();

# Define where the scheduler is that we want the information for
$globals['SCHEDULER_ADDRESS'] = "scheduler.irap-dev.org";

# Define which port the scheduler is listening on.
$globals['SCHEDULER_PORT'] = 3901;

$globals['SCHEDULER_QUEUE'] = "php_sdk_testing";
