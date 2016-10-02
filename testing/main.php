<?php

require_once(__DIR__ . '/../bootstrap.php');

function main()
{
    $num_failed = 0;
    
    $tests = array(
        new TestTaskTimeout(),
        new TestDependencies(),
        new TestSharedDependencies(),
        new TestBlockageRating(),
        new TestPriorities(),
        new TestMemoryUsage(),
        new TestPerformance()
    );

    foreach ($tests as $test)
    {
        print "Running " . get_class($test) . PHP_EOL;
        /* @var $test TestAbstract */
        $was_success = $test->run();
        
        if (!$was_success)
        {
            $num_failed++;
            echo $test->getErrorMessage() . PHP_EOL;
        }
    }
    
    if ($num_failed == 0)
    {
        print "Congratulations! All tests succeeded." . PHP_EOL;
    }
    else 
    {
        print $num_failed . " tests failed!" . PHP_EOL;
    }
}

main();