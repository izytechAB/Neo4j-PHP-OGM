<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event preRemove
 */
class PreRemove extends PersistEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'preRemove';
    }
} 
