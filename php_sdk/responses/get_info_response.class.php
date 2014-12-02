<?php

/* 
 * 
 */


class GetInfoResponse extends BaseResponse
{
    public function getAvailableTasks()
    {
        $cargo = $this->getCargo();
        return $cargo['available_tasks'];
    }
    
    
    public function getWaitingTasks()
    {
        $cargo = $this->getCargo();
        return $cargo['dependencies'];
    }
    
    
    public function getProcessingTasks()
    {
        $cargo = $this->getCargo();
        return $cargo['processing_tasks'];
    }
    
    
    public function getTasks()
    {
        $cargo = $this->getCargo();
        return $cargo['tasks'];
    }
}

