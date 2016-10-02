<?php

/* 
 * 
 */

class AddTaskResponse extends BaseResponse
{
    public function getTaskId()
    {
        $taskId = null;
        
        # This will throw an error if the response came back with an error
        $cargo = $this->getCargo();
        $taskId = $cargo['task_id'];
        return $taskId;
    }
}