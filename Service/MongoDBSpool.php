<?php
namespace Riconect\MailerBundle\Service;

/**
 * Class MongoDBSpool
 */
class MongoDBSpool extends \Swift_ConfigurableSpool
{
    
    /**
     * @abstract object $dm
     */
    protected $dm;
    
    /**
     * @var string $documentClass
     */
    protected $documentClass;
    
    /**
     * @var boolean $keep_emails
     */
    protected $keep_emails = false;
    
    /**
     * 
     */
    public function __construct($doctrine_mongodb, $config = [])
    {
        $this->dm = $doctrine_mongodb->getManager();
        $this->documentClass = $config['message_class'];
        $this->keep_emails = $config['keep_sent_emails'];
        
    }
    
    /**
     * Starts Spool mechanism.
     */
    public function start()
    {
        
    }
    
    /**
     * Stops Spool mechanism.
     */
    public function stop()
    {
        
    }
    
    /**
     * Tests if Spool mechanism has started.
     *
     * @return boolean
     */
    public function isStarted()
    {
        return true;
    }
    /**
     * Queues a message.
     *
     * @param \Swift_Mime_Message $message The message to store
     * @return boolean
     * @throws \Exception
     */
    public function queueMessage(\Swift_Mime_Message $message)
    {
        $document = new $this->documentClass;
        $document->setMessage(serialize($message));
        $document->setCreated(new \DateTime());
        $document->setStatus('message');
        
        $dm = $this->dm;
        try
        {
            $dm->persist($document);
            $dm->flush();
        }
        catch (\Exception $e)
        {
            throw new \Exception($e);
        }
        return true;
    }
    /**
     * Sends messages using the given transport instance.
     *
     * @param \Swift_Transport $transport         A transport instance
     * @param string[]        &$failedRecipients An array of failures by-reference
     *
     * @return int The number of sent emails
     */
    public function flushQueue(\Swift_Transport $transport, &$failedRecipients = null)
    {
        
        // TODO  Fetch messages with status 'error' and 'sendind' (with time ago) for retry.
        
        
        if (!$transport->isStarted())
        {
            $transport->start();
        }
        
        $limit = $this->getMessageLimit() ? $this->getMessageLimit() : 1000;
        
        $messages = $this->dm->createQueryBuilder($this->documentClass)
                ->hydrate(false)
                ->field('status')->equals('message')
                ->sort('created', 'asc')
                ->limit($limit)
                ->getQuery()
                ->execute();
               
        
        if (empty($messages)) {
            return 0;
        }
        
        $failedRecipients = (array) $failedRecipients;
        $count = 0;
        $time = time();
        
        foreach ($messages as $message)
        {
            $this->status($message['_id'], 'sending');
            
            $email = unserialize($message['message']);
            try
            {
                $count += $transport->send($email, $failedRecipients);
                $this->status($message['_id'], 'complete');
                
            }
            catch (\Exception $e)
            {
                $this->status($message['_id'], 'error');
            }
           
            if ($this->getTimeLimit() && (time() - $time) >= $this->getTimeLimit())
            {
                break;
            }
            
        }
        
        if (!$this->keep_emails)
        {
    
            // Delete sent emails.
            $this->dm->createQueryBuilder($this->documentClass)
                ->remove()
                ->field('status')->equals('complete')
                ->getQuery()
                ->execute();
        }
        
        
        return $count;
    }
    
    /**
     * Change status of Message Document
     * 
     * @param string $message_id Document Id
     * @param string $status Status to be changed
     */
    private function status($message_id, $status)
    {
        $this->dm->createQueryBuilder($this->documentClass)
            ->update()
            ->field('_id')->equals($message_id)
            ->field('status')->set($status)
            ->getQuery()
            ->execute();
    }
    
    
}