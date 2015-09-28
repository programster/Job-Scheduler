<?php


/**
 * This test checks that performance is adequate. It does not care about the order of tasks going
 * in or coming out, but just checks that they go in and out fast enough.
 * 
 */

class TestPerformance extends TestAbstract
{
    const TIME_LIMIT = 3;
    private $m_time_taken;
    
    public function __construct()
    {
        
    }
    
    
    public function getErrorMessage() 
    {
        return "TestPerformance: Scheduler was not quick enough. " . PHP_EOL .
               "Have you made sure to disable DEBUG mode on the server?" . PHP_EOL .
               "Time taken [" . $this->m_time_taken . "]" . PHP_EOL .
               "Time limit [" . self::TIME_LIMIT . "]";
    }

    
    public function test() 
    {
        $NUM_TASKS = 1000;
        
        $time_start = microtime($asFloat=true);
        
        $this->add_tasks($NUM_TASKS);
        $this->fetch_tasks($NUM_TASKS);
        
        $time_end = microtime($asFloat=true);
        
        $this->m_time_taken = $time_end - $time_start;
        
        if ($this->m_time_taken > self::TIME_LIMIT)
        {
            $this->m_successful = false;
        }
        else
        {
            print "Time taken: " . $this->m_time_taken . PHP_EOL;
            $this->m_successful = true;
        }
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

                # qucik and dirty to prog (not optimal)
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
    
    
    /**
     * Fetches the tasks we just added. This will mark the test as a failure if we fail
     * to fetch any of the tasks.
     * @param int $num_tasks - the number of tasks to fetch (the number we added)
     * @return void
     */
    private function fetch_tasks($num_tasks)
    {
        $scheduler = $this->getScheduler();
        
        for ($s=0; $s<$num_tasks; $s++)
        {
            try
            {
                $task = $scheduler->fetchTask();
                $scheduler->markTaskCompleted($task->getId(), $task->getLock());
            }
            catch(Exception $e)
            {
                $this->m_successful = false;
            }
        }        
    }
}