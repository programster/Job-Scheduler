/*
 * This is an object to act as a "one thread per connection" architecture. If we want to use thread
 * pools then please use the SocketThreadPoolHandler object instead.
 */

import java.net.Socket;

public class SocketConnectionHandler extends Thread
{
    private SocketConnection m_connection;
    
    public SocketConnectionHandler(Socket socket)
    {
        m_connection = new SocketConnection(socket);
    }
    
    
    @Override
    public void run()
    {
        while (m_connection.isClosed() == false)
        {
            try
            {
                // This will automatically wait/block when nothing is available.
                HandlerLogic.handleSocket(this.m_connection);
            }
            catch (Exception e)
            {
                System.out.println("SocketConnectionHandler: " + e);
            }
        }
    }
}