
import java.net.Socket;

/*
 * This is an thread that belongs in a "pool" that handles socket connection.
 * Using this architecture prevents the possibility that we overload our server with threads and 
 * also removes the overhead of spawning threads constantly.
 */


public class SocketThreadPoolHandler extends Thread
{
    private static SocketListener s_listener = null;
    
    public SocketThreadPoolHandler(SocketListener listener)
    {
        s_listener = listener;
    }
    
    public void run()
    {
        while (true)
        {
            try
            {
                // This will automatically wait/block when nothing is available.
                Socket socket = s_listener.getWaitingSocket();
                HandlerLogic.handleSocket(socket);
            }
            catch (Exception e)
            {
                System.out.println("SocketThreadPoolHandler: " + e.getMessage().toString());
            }
        }
    }
}
