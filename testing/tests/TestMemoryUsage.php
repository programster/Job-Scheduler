<?php


/**
 * This test checks that memory usage is adequate. It does not care about the order of tasks going
 * in or coming out, but just checks that they go in and out fast enough.
 * 
 */

class TestMemoryUsage extends TestAbstract
{
    const NUM_TASKS = 1000;
    const MAX_MEGABYTES = 128;
    
    private $m_utilization = 0; # will hold how much memory was used.
    
    public function __construct()
    {
        
    }
    
    
    public function getErrorMessage() 
    {
        return "TestMemoryUsage: Scheduler was not light enough. " . PHP_EOL .
               "MB Used [" . $this->m_utilization . "]" . PHP_EOL .
               "MB Limit [" . self::MAX_MEGABYTES . "]";
    }

    
    public function test() 
    {
        $this->add_tasks(self::NUM_TASKS);
        #1048576 = 1024 * 1024 (bytes > kb > mb)
        $this->m_utilization = (memory_get_usage($real_usage = true) / 1048576.0);
        $this->fetch_tasks(self::NUM_TASKS);
        
        if ($this->m_utilization > self::MAX_MEGABYTES)
        {
            $this->m_successful = false;
        }
        else
        {
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
            $jobs[] = $scheduler->addTask('task', $context=array(), $dependencies, $priority);
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