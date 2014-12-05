/*
 * This is where all the logic for the scheduling of tasks should be. This holds all the tasks
 * and all references to them.
 * We use a singleton so that any calss can access this objects easily and prevent duplicate 
 * managers.
 *
 * NOTE ON synchronization
 * All methods that have the synchronized attribute share the SAME lock. Thus two different methods
 * that are both synchronized cannot run simultaneously. This is highly desirable as it means
 * there is no risk of "corruption" across the verious member variable collections we have since
 * they closely depend upon each other.
 * Static methods have a lock that is tied to the class rather than the object.
 */

import com.google.gson.Gson;
import com.google.gson.JsonArray;
import com.google.gson.JsonObject;
import com.google.gson.JsonPrimitive;
import java.util.ArrayList;
import java.util.Collection;
import java.util.HashMap;
import java.util.Hashtable;
import java.util.Iterator;
import java.util.Set;
import java.util.concurrent.ConcurrentHashMap;



public class Scheduler 
{
    private int m_taskCounter;
    
    private static Scheduler s_instance;
    
    // Map of task IDs to the task objects themselves
    
    // HashTable is threadsafe, hashmap is not
    private ConcurrentHashMap<Integer, Task> m_tasks = new ConcurrentHashMap<>();
    
    // Jobs sorted by priority, then time created using objects comparator.
    private LateBlockingQueue<Task> m_availableTasks = new LateBlockingQueue<>();
    
    // Map of task ids being processed (values are not used)
    private ConcurrentHashMap<Integer, Integer> m_processingTasks = new ConcurrentHashMap<>();
    
    // Map of Task IDs to the list of Task IDs that depend upon it being completed.
    private ConcurrentHashMap<Integer, ArrayList<Integer>> m_dependencies = new ConcurrentHashMap<>();
    
    
    private static SocketListener s_socketListener;
    
    /**
     * Be careful to only initiate one of this object, but we do not NEED to implement a singleton
     */
    private Scheduler()
    {
        m_taskCounter = 0;
    }
    
    
    /**
     * Fetches the instance of this class (singleton)
     * @return Scheduler $s_instance - the instance of this class.
     */
    public static Scheduler getInstance()
    {
        if (s_instance == null)
        {
            s_instance = new Scheduler();
        }
        
        return s_instance;
    }
   
    
    /**
     * Creates and adds a task to this scheduler based on the provided parameters.
     * @param name - a name to give the task
     * @param dependencies - array of task ids that this newly created task is reliant on 
     *                              having been finished before it can execute.
     * @param extraInfo - any extra information to attach to the task. E.g a json string
     *                            of name/value pairs to give context to the task.
     * @param priority - integer between 1 and 10 which affects the scheduling of this task. 
     *                        (higher = more important, default 5). Try to avoid using.
     * @param group - optional string that can be used to group tasks together. This allows group
     *                 based actions, such as the ability to remove all related tasks at once etc.
     * @return taskId - the id of the task that was just added
     */
    public synchronized int addTask(String name, 
                                    ArrayList<Integer> dependencies, 
                                    String extraInfo, 
                                    int priority,
                                    String group)
    {
        int taskId = m_taskCounter;
        m_taskCounter++;
        
        
        // Task may list as dependencies tasks that have already completed and been removed so 
        // remove these
        HashMap<Integer, Integer>remainingDependencies = new HashMap<>();
        
        for (Integer dependencyId : dependencies) 
        {
            if (m_tasks.containsKey(dependencyId))
            {
                remainingDependencies.put(dependencyId, 1); // dependencies are keyed by id
            }            
        }
  
        Task newTask = new Task(taskId, name, remainingDependencies, extraInfo, priority, group);
        m_tasks.put(taskId, newTask);
        
        if (remainingDependencies.isEmpty())
        {
            System.out.println("Task has no dependencies so adding to the available tasks list");
            m_availableTasks.add(newTask);
        }
        else
        {
            System.out.println("Task has dependencies adding to m_dependencies");
            // Task is not available so we need to put it in the relevant dependency area.
            Set dependency_ids = remainingDependencies.keySet();
            
            Iterator it = dependency_ids.iterator();
            
            while (it.hasNext()) 
            {
                // referring to the task(s) that need to execute before this an "impediment"
                int impedimentTaskId = (int)it.next();
                
                if (!m_dependencies.containsKey(impedimentTaskId))
                {
                    // The prerequisite task does not yet have a list of waiting tasks.
                    ArrayList<Integer> impedimentsDependencies = new ArrayList<>();
                    impedimentsDependencies.add(newTask.getId());
                    m_dependencies.put(impedimentTaskId, impedimentsDependencies);
                }
                else
                {
                    ArrayList<Integer> impedimentsDependencies = m_dependencies.get(impedimentTaskId);
                    impedimentsDependencies.add(newTask.getId());
                    
                    // Not sure if that by updating the arraylist I do not need to do this (references)
                    m_dependencies.put(impedimentTaskId, impedimentsDependencies);                    
                }    
            }
        }
        
        return taskId;
    }
    
    
    /**
     * Returns flag indicating whether this scheduler has jobs available to be worked upon. This 
     * should be called before getJob in order to prevent errors/exceptions.
     * @return boolean flag indicating whether this scheduler has jobs available
     */
    public synchronized boolean isJobAvailable()
    {
        boolean jobAvailable = false;
        
        if (!m_availableTasks.isEmpty())
        {
            jobAvailable = true;
        }
        
        return jobAvailable;
    }
    
    
    /**
     * Print to std out what the currently available tasks are.
     */
    private void outputAvailableTasks()
    {
        try
        {
            ArrayList<Task> tasks = m_availableTasks.getArrayList();
            System.out.println("Available tasks:");
        
            for (Task task : tasks)
            {
                System.out.println(task.getId());
            }

            System.out.println("--------");
        }
        catch (Exception e)
        {
            System.out.println("Unable to get tasks as an array");
        }
    }
    
    
    /**
     * Fetches the next job to be performed from the prioritised queue and adds it to the list of 
     * jobs currently being processed.
     * We keep this function synchronized because take() will wait, so we need one thread to
     * check if the queu is empty before taking from it otherwise two thread may try and take the
     * last job, resulting in one getting blocked.
     * 
     * @return Job availableJob - the first available job. Will return null if none are.
     * @throws Exception if m_availableTasks is empty
     */
    public synchronized Task getAvailableTask() throws Exception
    {
        System.out.println("Scheduler fetching first available task.");
        
        Task availableJob = m_availableTasks.poll();
        
        if (availableJob != null)
        {
            availableJob.lock();
            m_processingTasks.put(availableJob.getId(), 1);
        }
        else
        {
            // check to see if any of the processing tasks have exceeded their lock time limit
            System.out.println("No tasks available, seeing if any timed out.");
            long timeNow = Core.time(true);
            boolean unlockedTask = false;
            
            // Dont forget that m_processingTasks is a hash table so the value is always 1
            Set processing_task_ids = m_processingTasks.keySet();
            
            for (Object processing_task_id_obj : processing_task_ids)
            {
                Integer processing_task_id = (Integer)processing_task_id_obj;
                Task processingTask = m_tasks.get(processing_task_id);
                
                long age = timeNow - processingTask.getLockTime();
                
                if (age > (Settings.MAX_LOCK_TIME * 1000)) // * 1k because lock time in secs not ms
                {
                    System.out.println("Unlocking a task that has passed age limit.");
                    rejectTask(processing_task_id, processingTask.getLock());
                    unlockedTask = true;
                }
            }
            
            if (unlockedTask)
            {
                availableJob = getAvailableTask();
            }
            else
            {
                System.out.println("Throwing exception. There are no available tasks!");
                throw new Exception("There are no available tasks!");
            }
        }
        
        System.out.println("Scheduler returning availableJob");
        return availableJob;
    }
    
    
    /**
     * Callback for when a task has been completed. This allows us to mark the job as completed, thus 
     * freeing other processes that had it as a dependency.
     * 
     * @param completed_task_id - the ID of the job we want to state has finished.
     * @param lock - the id of the lock which locked the task (proving correct server unlocking it)
     * @throws java.lang.Exception if the wrong lock was provided.
     */
    public synchronized void completeTask(Integer completed_task_id, String lock) throws Exception
    {
        Task job = m_tasks.get(completed_task_id);
        
        if (m_processingTasks.containsKey(completed_task_id) && job.getLock().equals(lock))
        {
            m_processingTasks.remove(completed_task_id);
            
            // Find the tasks that were dependent on that process being finished, and see if they 
            // are now available to start work.
            if (m_dependencies.containsKey(completed_task_id))
            {
                ArrayList<Integer> dependent_task_ids = m_dependencies.get(completed_task_id);
                
                for (Integer dependent_task_id : dependent_task_ids) 
                {
                    Task dependent_task = m_tasks.get(dependent_task_id);
                    dependent_task.removeDependency(completed_task_id);
                    
                    if (dependent_task.isReady())
                    {
                        m_availableTasks.add(dependent_task);
                    }
                }
            
                m_dependencies.remove(completed_task_id);
            }

            // Remove the task object from the system at the very end.
            m_tasks.remove(completed_task_id);
        }
        else
        {
            // Throw an exception here
            // If you get this error, it is likely that two things completed a task because your
            // MAX_LOCK_TIME is too short and a task took longer than that to complete.
            throw new Exception("Incorrect lock provided or task not in processing tasks list.");
        }
    }
    
    
    /**
     * Function to return information about this object for general info on the schedule
     * @return info - this object in JsonObject form.
     */
    public synchronized JsonObject getInfo()
    {
        JsonObject info = new JsonObject();
        Collection tasksCollection = m_tasks.values();
        Collection processingTasks = m_processingTasks.values();
        Collection dependencies = m_dependencies.values();
        
        info = addTaskCollectionToJsonObject(info, "tasks",            tasksCollection);
        info = addTaskCollectionToJsonObject(info, "available_tasks",  m_availableTasks);
        info = addTaskCollectionToJsonObject(info, "processing_tasks", processingTasks);
        info = addTaskCollectionToJsonObject(info, "dependencies",     dependencies);
        info = addTaskCollectionToJsonObject(info, "tasks",            tasksCollection);
        
        return info;
    }
    
    
    /**
     * Given a collection of Tasks, serialize them and put them into a JSON object with the 
     * specified name.
     * @param obj - the json Object we are adding the list to
     * @param name - the name of the list/collection
     * @param collection - the collection that has all the tasks.
     * @return obj - the modified json object
     */
    private JsonObject addTaskCollectionToJsonObject(JsonObject obj, 
                                                     String name, 
                                                     Collection collection)
    {
        Gson gson = new Gson();
        JsonArray serializedTaskArray = new JsonArray();
        
        Iterator it = collection.iterator();
        
        while (it.hasNext())
        {
            Task task = (Task)it.next();
            JsonPrimitive element = new JsonPrimitive(task.jsonSerialize().toString());
            serializedTaskArray.add(element);
        }
        
        obj.add(name, serializedTaskArray);
        
        return obj;
    }
    
    
    /**
     * Given a collection of Tasks, serialize them an put them into a json object with the 
     * specified name.
     * @param obj - the json Object we are adding the list to
     * @param name - the name of the list/collection
     * @param collection - the collection that has all the tasks.
     * @return obj - the modified json object
     */
    private JsonObject addIntegerCollectionToJsonObject(JsonObject obj, 
                                                        String name, 
                                                        Collection collection)
    {
        Gson gson = new Gson();
        ArrayList<String> serializedTaskArray = new ArrayList<>(); 
        
        for (Object taskIdObject : collection)
        {
            Integer taskId = (Integer) taskIdObject;

            Task task = m_tasks.get(taskId);
            serializedTaskArray.add(task.jsonSerialize().getAsString());
        }
       
        String serializedTasksString = gson.toJson(serializedTaskArray);
        obj.add(name, new JsonPrimitive(serializedTasksString));
        
        return obj;
    }
    
    
    /**
     * Fetches a list of all the task IDs that are dependent on the specified task being finished
     * before they can be marked as available to work on.
     * @param task_id - the id of the task we wish to fetch the dependency list for
     * @return array list of task IDs dependent on the specified task being completed.
     */
    public synchronized ArrayList<Integer> getDependencyList(Integer task_id)
    {
        ArrayList<Integer> dependents = new ArrayList();
        
        if (m_dependencies.containsKey(task_id))
        {
            dependents = m_dependencies.get(task_id);
        }
        
        return dependents;
    }
    
    
    /**
     * Converts a task ID into a Task object. THis only works if the task is in this objects tasks
     * list.
     * @param task_id - the ID of the task we want to fetch
     * @return Task the task that has the specified ID.
     * @throws Exception if the task with that ID does not exist.
     */
    public synchronized Task getTaskFromId(Integer task_id) throws Exception
    {
        Task task = null;
        
        if (m_tasks.containsKey(task_id))
        {
            task = m_tasks.get(task_id);
        }
        else
        {            
            throw new Exception("get_task_from_id: specified task id does not exist.");
        }
        
        return task;
    }
    
    
    /**
     * Rejects the task. This results in the task being moved from the processing list to the 
     * available task list and re-sorting the available task list.
     * @param rejected_task_id - the id of the task we are rejecting
     * @param lock - the lock that was used to lock the task when fetched.
     * @throws java.lang.Exception if the lock provided was incorrect.
     */
    public synchronized void rejectTask(Integer rejected_task_id, String lock) throws Exception
    {
        System.out.println("Rejecting task.");
        
        if (!m_processingTasks.containsKey(rejected_task_id))
        {
            System.out.println("Task not found in processing list.");
            throw new Exception("Task not found in processing list."); 
        }
        
        System.out.println("Fetching rejected task");
        Task rejectedTask = m_tasks.get(rejected_task_id);
                
        if (rejectedTask.unlock(lock))
        {
            System.out.println("Removing rejected task from processing tasks");
            m_processingTasks.remove(rejected_task_id);
            m_availableTasks.add(rejectedTask);
        }
        else
        {
            // Throw an exception here
            throw new Exception("The lock provided was incorrect");
        }
    }
    
    
    /**
     * Rejects the task. This results in the task being moved from the processing list to the 
     * available task list and re-sorting the available task list.
     * @param task_id - the id of the task we are rejecting
     * @throws java.lang.Exception if the lock provided was incorrect.
     */
    public synchronized void removeTask(Integer task_id) throws Exception
    {
        System.out.println("Removing task.");
        
        if (!m_tasks.containsKey(task_id))
        {
            System.out.println("Task not found!");
            throw new Exception("Task not found!"); 
        }
        
        System.out.println("Fetching task to remove");
        Task removalTask = m_tasks.get(task_id);
        
        if (m_processingTasks.containsKey(task_id))
        {
            System.out.println("Removing task from processing tasks");
            m_processingTasks.remove(task_id);
        }
        
        // remove the task from the available tasks list if it exists
        m_availableTasks.remove(removalTask);
        
        if (m_dependencies.containsKey(task_id))
        {
            m_dependencies.remove(task_id);
        }
    }
    
    
    /**
     * Remove all the tasks that belong to the specified group.
     * Perhaps one day we will have an additionl collection for lookups of this.
     * @param groupName 
     * @throws Exception if we tried to remove a task that does not exist. This should never happen
     */
    public synchronized void removeGroup(String groupName) throws Exception
    {
        Collection<Task> tasks = m_tasks.values();
        ArrayList<Integer> tasksToRemove = new ArrayList<>();
        
        for (Task task : tasks) 
        {
            if (task.getGroup().equals(groupName))
            {
                // we dont just remove the task from inside the iterator because that would change
                // the collection whilst we are in the midst of looping over it.
                tasksToRemove.add(task.getId());
            }
        }
        
        for (Integer task_id : tasksToRemove)
        {
            this.removeTask(task_id);
        }

        System.out.println("--------");
    }
}