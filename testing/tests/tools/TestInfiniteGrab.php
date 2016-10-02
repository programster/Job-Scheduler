<?php


/**
 * This is a script for manual testing by the developer that allows them to have a script 
 * infinitely grabbing tasks and makrking them as completed.
 */

require_once(__DIR__ . '/../../../bootstrap.php');

class TestInfiniteGrab extends TestAbstract
{
    private $m_recieved_tasks = array(); # store the order in which the tasks came back, name only
    
    public function getErrorMessage() 
    {
        $message = get_class() . "?" . PHP_EOL;        
        return $message;
    }

    
    /**
     * The main logic of the test.
     * Note that the successful flag gets updated from within the fetch_expected_task function
     */
    public function test() 
    {
        global $globals;
        
        $scheduler = $this->getScheduler();
        
        # Now fetch the tasks and hope that they come in the correct order.
        $this->m_successful = true; # default to true and let the funcs below set to false if fail
        
        while (true)
        {
            $this->clear_scheduler();
        }
    }
}

$test = new TestInfiniteGrab();
$test->run();