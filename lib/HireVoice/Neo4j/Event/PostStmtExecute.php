<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event postStmtExecute
 */
class PostStmtExecute extends StmtEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'postStmtExecute';
    }
} 
