<?php

/* 
 * 
 */

namespace iRAP\JobScheduler\Responses;


class GetTaskResponse extends BaseResponse
{
    public function getTask()
    {
        $cargo = $this->getCargo();
        $task = Task::createFromArray($cargo['task']);
        return $task;
    }
}