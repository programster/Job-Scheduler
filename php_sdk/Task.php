<?php

/*
 * This is the task that is fetched from a get_task request. 
 */

namespace iRAP\JobScheduler;


class Task
{    
    private $m_id;
    private $m_name;
    private $m_creationTime;
    private $m_priority;
    private $m_lock;
    private $m_extraInfo;
    
    private function __construct() {}
    
    
    public static function createFromArray($taskArray)
    {
        $task = new Task();
        
        $task->m_id             = $taskArray['id'];
        $task->m_name           = $taskArray['name'];
        $task->m_creationTime   = $taskArray['creation_time'];
        $task->m_priority       = $taskArray['priority'];
        $task->m_lock           = $taskArray['lock'];
        $task->m_extraInfo      = $taskArray['extra_info'];
        
        return $task;
    }
    
    
    // Accessor functions
    public function getId()             { return $this->m_id; }
    public function getName()           { return $this->m_name; }
    public function getLock()           { return $this->m_lock; }
    public function getPriority()       { return $this->m_priority; }
    public function getCreationTime()   { return $this->m_creationTime; }
    public function getLockTime()       { return $this->m_lockTime; }
    public function getExtraInfo()      { return $this->m_extraInfo; }
}

