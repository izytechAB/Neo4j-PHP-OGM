<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event preStmtExecute
 */
class PreStmtExecute extends StmtEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'preStmtExecute';
    }
} 
