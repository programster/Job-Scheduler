
import java.net.Socket;

/*
 * This is an object to act as a "one thread per connection" architecture. If we want to use thread
 * pools then please use the SocketThreadPoolHandler object instead.
 * 
 */

/**
 * 
 */
public class SocketConnectionHandler extends Thread
{
    private Socket m_socket;
    
    public SocketConnectionHandler(Socket socket)
    {
        m_socket = socket;
    }
    
    
    @Override
    public void run()
    {
        try
        {
            // This will automatically wait/block when nothing is available.
            HandlerLogic.handleSocket(this.m_socket);
        }
        catch (Exception e)
        {
            System.out.println("SocketConnectionHandler: " + e);
        }
    }
}
