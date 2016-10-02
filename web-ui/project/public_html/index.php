<?php

require_once(__DIR__ . '/../libs/bootstrap.php');

/*
 * Due to the fact that each process can have multiple dependencies, most javascript charting 
 * solutions are not approprate. However, the following may:
 * http://mbostock.github.io/protovis/ex/force.html
 */


function display_avaialable_tasks(Array $available_tasks, Array $dependencies, Array $tasks)
{    
    $html = '<h2>Available Tasks</h2>';
    
    if (count($available_tasks) > 0)
    {

        $names = array();

        foreach ($available_tasks as $task_id)
        {
            $task = $tasks[$task_id];
            $name = $task['name'];

            if (isset($names[$name]))
            {
                $names[$name]++;
            }
            else
            {
                $names[$name] = 1;
            }
        }

        $tableRows = '';
        foreach ($names as $task_name => $count)
        {
            $tableRows .= '<tr><td>' . $task_name . '</td><td>' . $count . '</td></tr>';
        }

        $html .= 
            '<table border=1 padding="5px">' .
                '<tr>' .
                    '<th>Name</th>' .
                    '<th>Number</th>' .
                '</tr>' .
                $tableRows .
            '</table>';
    }
    else
    {
        $html .= '<p>None</p>';
    }
    
    return $html;
}



/** 
 * Generates the html for rendering the the tasks currently being processed in a human readable form.
 * @param type $processing_tasks - array of all the tasks that are currently executing
 * @param type $dependencies - array of all the dependencies in the scheduler.
 */
function display_processing_tasks(Array $processing_tasks, Array $dependencies, Array $tasks)
{
    $html = '<h2>Processing Tasks</h2>';
    
    if (count($processing_tasks) > 0)
    {
        $names = array();
    
        foreach ($processing_tasks as $processing_task_id)
        {
            $processing_task = $tasks[$processing_task_id];
            $name = $processing_task['name'];

            if (isset($names[$name]))
            {
                $names[$name]++;
            }
            else
            {
                $names[$name] = 1;
            }
        }

        $tableRows = '';
        foreach ($names as $task_name => $count)
        {
            $tableRows .= '<tr><td>' . $task_name . '</td><td>' . $count . '</td></tr>';
        }

        $html .= 
            '<table border=1 padding="5px">' .
                '<tr>' .
                    '<th>Name</th>' .
                    '<th>Number</th>' .
                '</tr>' .
                $tableRows .
            '</table>';
    }
    else
    {
        $html .= '<p>None</p>';
    }
    
    return $html;
}


/**
 * Generates the html for rendering the the dependencies in a human readable form.
 * @param type $dependencies array of all the dependencies
 */
function display_dependencies(Array $dependencies, Array $tasks)
{
    $html = 
        '<h2>Waiting Tasks</h2>';
    
    $waitingTasks = array();
    
    foreach ($dependencies as $impediment_task_id => $depdendent_task_ids)
    {
        foreach ($depdendent_task_ids as $dependent_task_id)
        {
            $dependent_task = $tasks[$dependent_task_id];
            
            if (isset($waitingTasks[$dependent_task_id]))
            {
                $waitingTasks[$dependent_task_id]['dependencies'][] = $impediment_task_id;
            }
            else
            {
                $waitingTasks[$dependent_task_id] = array(
                    'name'          => $dependent_task['name'], 
                    'dependencies'  => array($impediment_task_id)
                );
            }
        }
    }
    
    $html .= print_r($waitingTasks, true);
    return $html;
}


function main()
{
    global $globals;

    $scheduler_communicator = new SchedulerCommunicator($globals['SCHEDULER_ADDRESS'], 
                                                        $globals['SCHEDULER_PORT']);
    $response = $scheduler_communicator->getInfo();

    if ($response['result'] == 'success')
    {
        $info = $response['cargo'];
        
        $tasks            = $info['tasks'];
        $available_tasks  = $info['available_tasks'];
        $dependencies     = $info['dependencies'];
        $processing_tasks = $info['processing_tasks'];
        
        print display_avaialable_tasks($available_tasks, $dependencies, $tasks);
        print display_processing_tasks($processing_tasks, $dependencies, $tasks);
        print display_dependencies($dependencies, $tasks);
    }
    else
    {
        print "ERROR - Failed to connect to the scheduler: " . $response['message'];
    }
}

main();



