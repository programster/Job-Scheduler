
<?php

# This is responsible for initializing everything. Please do NOT think of this as
# just an 'includes' file, although this replaces that. As many includes as possible
# should be replaces by making use of the autoloader.

require_once(__DIR__ . '/../../settings.php');
require_once(__DIR__ . '/core.class.php'); # autoloader requires core to work.
require_once(__DIR__ . '/auto_loader.class.php');