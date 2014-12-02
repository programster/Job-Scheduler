<?php


/**
 * This is a tool for a developer to manually run in order to have a script infinitely adding
 * tasks to the scheduler for stress testing.
 */

require_once(__DIR__ . '/../../../bootstrap.php');

class TestInfiniteAdd extends TestAbstract
{
    private $m_recieved_tasks = array(); # store the order in which the tasks came back, name only
    
    public function getErrorMessage() 
    {
        $message = get_class() . " Unknown" . PHP_EOL;
        
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
        
        while (true)
        {
            $this->add_tasks(1000);
        }
        
        # Now fetch the tasks and hope that they come in the correct order.
        $this->m_successful = true; # default to true and let the funcs below set to false if fail
    }


    /**
     * Adds a series of random tasks to the scheduler. These have random dependencies and priorities
     * @param int $num_tasks - the number of tasks to add.
     * @return void
     */
    private function add_tasks($num_tasks)
    {
        $scheduler = $this->getScheduler();
        $jobs = array();
        
        for ($s=0; $s<$num_tasks; $s++)
        {
            $dependencies = array();
            $numJobs = count($jobs);
            # May want to randomly set dependencies here...
            $maxNumDependencies = $numJobs;
            
            if ($maxNumDependencies > 10)
            {
                $maxNumDependencies = 10;
            }

            $numDependencies = rand(0, $maxNumDependencies);

            for ($t=0; $t<$numDependencies; $t++)
            {
                $chosenIndex = null;
                $chosenIndexes = array();

                # quick and dirty to prog (not optimal)
                while ($chosenIndex === null)
                {
                    $chosenIndex = rand(0, $numJobs-1);

                    if (!isset($chosenIndexes[$chosenIndex]))
                    {
                        $dependencies[] = $jobs[$chosenIndex];
                        $chosenIndexes[$chosenIndex] = true;
                    }
                    else
                    {
                        $chosenIndex = null;
                    }
                }
            }

            $priority = rand(1,10);
            $jobs[] = $scheduler->addTask('random_task', $context=array(), $dependencies, $priority);
        }        
    }
}

$test = new TestInfiniteAdd();
$test->run();