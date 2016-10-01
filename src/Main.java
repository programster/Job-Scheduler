
import java.io.File;



class Main
{
    public static void main (String[] args)
    {
        try
        {
            File path = new java.io.File( "." );
            
            if (!Core.isAlreadyRunning(path, "lock.txt"))
            {
                Debug.println("Debug mode enabled.");
                SocketListener socketListener = new SocketListener();
                socketListener.start();
                System.out.println("started the socket listener");
            }
            else
            {
                System.out.println("Scheduler already running so quitting.");
            }
        }
        catch(Exception e)
        {
            System.out.println("Failed to get the JAR path " + e.toString());
        }
    }
}