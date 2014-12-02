
import java.util.ArrayList;
import java.util.Collection;
import java.util.Collections;
import java.util.Iterator;
import java.util.NoSuchElementException;
import java.util.Queue;

/*
 * This is a collection that does not sort elements when they are added, but performs a search
 * for the min element whenever we try to fetch the head of the queue. 
 * You only want to use this when the object's order/priority (defined by the objects comparison
 * method) is likely to change after having been added to this collection and so the "order" needs
 * to be evaluated as late as possible.
 * For these reasons, we do not perform sorts, but perform searches for the min element.
 * Please refer to:
 * http://stackoverflow.com/questions/22617080/java-threadsafe-collection-that-sorts-on-remove-take/22617265?noredirect=1#comment34440355_22617265
 */


public class LateBlockingQueue<E extends Comparable<E>> implements Queue<E>
{
    private ArrayList<E> m_list;
    
    public LateBlockingQueue()
    {
        m_list = new ArrayList<>();
    }
    
    @Override
    public synchronized E poll() 
    {
        E element = null;
        
        if (!m_list.isEmpty())
        {
            if (true)
            {
                element = Collections.min(m_list);
                remove(element);
            }
            else
            {
                Collections.sort(m_list);
                element = m_list.remove(0);
            }
        }
        
        return element;
    }
    
    @Override
    public synchronized E[] toArray() 
    {
        Collections.sort(m_list);
        return (E[]) m_list.toArray();
    }
    
    public synchronized ArrayList getArrayList()
    {
        return m_list;
    }

    @Override
    public synchronized <E> E[] toArray(E[] a) 
    {
        Collections.sort(m_list);        
        return m_list.toArray(a);
    }
    
    /**
     * Retrieves and removes the head of this queue. This method differs from poll only in that 
     * it throws an exception if this queue is empty.
     */
    @Override
    public synchronized E remove() 
    {
        if (m_list.isEmpty())
        {
            throw new NoSuchElementException();
        }
        else
        {
            return poll();
        }
    }
    
    @Override
    public boolean remove(Object o) 
    {
        int start = m_list.size();
        return m_list.remove(o);
    }
    
    
    @Override
    public synchronized boolean removeAll(Collection<?> c) { return m_list.removeAll(c); }
    
    @Override
    public synchronized boolean add(E e) 
    {
        return m_list.add(e);
    }
    
    @Override
    public synchronized boolean offer(E e) 
    {
        throw new UnsupportedOperationException("Not supported yet."); //To change body of generated methods, choose Tools | Templates.
    }
    
    @Override
    public synchronized E element() {
        throw new UnsupportedOperationException("Not supported yet."); //To change body of generated methods, choose Tools | Templates.
    }

    @Override
    public synchronized E peek() {
        throw new UnsupportedOperationException("Not supported yet."); //To change body of generated methods, choose Tools | Templates.
    }
    
    @Override
    public synchronized int size() { return m_list.size(); }
    
    @Override
    public synchronized boolean isEmpty() { return m_list.isEmpty(); }
    
    @Override
    public synchronized boolean containsAll(Collection<?> c) { return m_list.containsAll(c); }
    
    @Override
    public synchronized boolean addAll(Collection<? extends E> c) { return m_list.addAll(c); }
    
    @Override
    public synchronized boolean retainAll(Collection<?> c) { return m_list.retainAll(c); }
    
    @Override
    public synchronized void clear() { m_list.clear(); }
    
    @Override
    public synchronized boolean contains(Object o) { return m_list.contains(o); }
    
    @Override
    public Iterator<E> iterator() 
    {
        return m_list.iterator();
    }
}
