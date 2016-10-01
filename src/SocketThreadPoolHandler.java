
import java.net.Socket;
import java.util.Iterator;
import java.util.concurrent.BlockingQueue;

/*
 * This is a "worker thread" that handles socket connections. There will be a pool of these workers
 * which work in parallel to handle all client connections. The number of connections will be
 * evenly distributed across these workers, with each worker being able to handle multiple 
 * connections.
 *
 * Using this architecture prevents the possibility that we overload our server with threads and 
 * also removes the overhead of spawning threads constantly.
 */


public class SocketThreadPoolHandler extends Thread
{
    private BlockingQueue<SocketConnection> m_clientSockets;
    private static SocketListener s_listener = null;
    
    
    public SocketThreadPoolHandler(SocketListener listener)
    {
        s_listener = listener;
    }
    
    
    /**
     * Add a client socket connection to this handlers collection that it is responsible for.
     * @param clientSocket 
     */
    public void addSocket(Socket clientSocket)
    {
        SocketConnection conn = new SocketConnection(clientSocket);
        m_clientSockets.add(conn);
    }
    
    
    /**
     * Return the number of sockets this thread is currently handling. Useful for the manager
     * to decide which worker it should give new connections to for even distribution.
     * @return the number of sockets
     */
    public int getSocketCount()
    {
        return m_clientSockets.size();
    }
    
    
    
    /**
     * Loop over our collection of connections and pass off any requests to the 
     * HandlerLogic for handling communication protocol
     */
    public void run()
    {
        while (true)
        {
            Iterator socketIterator = m_clientSockets.iterator();
            
            while (socketIterator.hasNext())
            {
                try
                {
                    // This will automatically wait/block when nothing is available.
                    SocketConnection connection = (SocketConnection)socketIterator.next();
                    HandlerLogic.handleSocket(connection);
                    
                    if (connection.isClosed())
                    {
                        socketIterator.remove();
                    }
                }
                catch (Exception e)
                {
                    System.out.println("SocketThreadPoolHandler: " + e.getMessage().toString());
                }
            }
        }
    }
}
