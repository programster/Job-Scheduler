
import java.io.File;
import java.io.RandomAccessFile;
import java.net.URISyntaxException;
import java.nio.channels.FileChannel;
import java.nio.channels.FileLock;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Calendar;
import java.util.UUID;

/*
 * This is just a 'core' library of useful functions for java.
 */

public class Core 
{
    public static String GetTimeString()
    {
        DateFormat dateFormat = new SimpleDateFormat("HH:mm:ss");
        Calendar cal          = Calendar.getInstance();
        String timeString     = (String)(dateFormat.format(cal.getTime()));
        
        return timeString;
    }
    
    
    /**
     * Get the current time, either in milliseconds or in seconds
     * @param getMilliseconds - set to true if you want accuracy to the milliseconds, false if
     *                           to the second
     * @return currrentTime - the current time.
     */
    public static long time(boolean getMilliseconds)
    {
        long currentTime = System.currentTimeMillis();
        
        if (!getMilliseconds)
        {
            currentTime = currentTime/1000;
        }
        
        return currentTime;
    }
    
    
    /**
     * Appends the amount of memory used at the current time to a file. Good for checking that there
     * isn't a memory leak.
     * @param void
     * @return void
     */
    public String GetResourceUsage()
    {
        Runtime runtime = Runtime.getRuntime();
        int timeInSeconds = (int)(System.currentTimeMillis() / 1000);
        int sizeMB = 1024 * 1024;
        float usedMemmory = (runtime.totalMemory() - runtime.freeMemory()) / sizeMB;
        String message = "" + timeInSeconds + " Used Memory: " + usedMemmory;

        return message;
    }


    /**
     * Fetches the number of cores on the system that the application is deployed on. This is
     * useful if you want to automatically manage thread pools.
     * @param void
     * @return int
     */
    public static int GetNumCores()
    {
        int cores = Runtime.getRuntime().availableProcessors();
        return cores;
    }
    

    /**
     * Fetches the path to the current class. This needs testing to see if it is returning
     * the path to the folder containing this core lib or the path to the file that called this
     * function.
     * @param void
     * @return String
     */
    public static String getPath()
    {
        String path = ClassLoader.getSystemClassLoader().getResource(".").getPath();
        return path;
    }
    
    
    /**
     * Generates a random string of the desired specified length based on UUID
     * http://docs.oracle.com/javase/7/docs/api/java/util/UUID.html
     * @param desiredLength
     * @return randomString
     */
    public static String generateRandomString(int desiredLength)
    {
        String randomString = "";
        UUID id;
        
        while (randomString.length() < desiredLength)
        {
            id = UUID.randomUUID();
            randomString += id.toString();
        }
        
        // Cut it down to the size we want.
        randomString = randomString.substring(0, (desiredLength-1));
        
        return randomString;
    }
    
    
    /**
     * Checks to see if this process is already running and if so, prevents this from running
     * @param lockDirPath - the absolute or relative path to the directory that should hold the lock
     * @param lockFileName - the name of the file that should act as the lock.
     * @return flag indicating whether already running.
     */
    public static boolean isAlreadyRunning(File lockDirPath, String lockFileName)
    {
        boolean isAlreadyRunning = false;
        String absoluteFilePath = lockDirPath + "/" + lockFileName;
        
        // Create the directory structure if it doesn't exist (including all relevant parents)
        if (!lockDirPath.exists())
        {
            lockDirPath.mkdirs();
        }
        
        // Create the file if it doesnt exist.
        File scriptFile = new File(absoluteFilePath);
        
        if (!scriptFile.exists())
        {
            try
            {
                scriptFile.createNewFile();
            }
            catch (Exception e)
            {
                System.out.println("Error creating lock file that didnt exist: " + e);
                System.exit(1);
            }
        }
        
        // If can get a lock on the file then this process is not already running.
        try
        {
            FileChannel channel = new RandomAccessFile(scriptFile, "rw").getChannel();           
            FileLock lock = channel.tryLock();
            
            // Trylock returns null if overlapping lock, not an error
            if (lock == null)
            {
                isAlreadyRunning = true;
            }
        }
        catch (Exception p)
        {
            System.out.println("checkIfAlreadyRunning Error " + p);
            System.exit(1);
        }
        
        return isAlreadyRunning;
    }
    
    
    /**
     * Returns the absolute path to the directory that contains this jar file for UNIX systems
     * where paths use / instead of windows which uses \
     * WARNING: only use this function if the code if the code is run through a jar.
     * @return the absolute path to the directory containing the jar.
     */
    public static String getJarPath() throws URISyntaxException
    {        
        File myfile = new File(Core.class.getProtectionDomain().getCodeSource().getLocation().toURI().getPath());
        
        String path = myfile.getAbsolutePath();
        
        // Remove the xxx.jar end part.
        int lastIndex = path.lastIndexOf('/');
        path = path.substring(0, lastIndex);
        
        return path;
    }
    
}
