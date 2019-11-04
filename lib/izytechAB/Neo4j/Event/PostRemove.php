<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event postRemove
 */
class PostRemove extends PersistEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'postRemove';
    }
} 
