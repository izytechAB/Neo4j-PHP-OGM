<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event prePersist
 */
class PrePersist extends PersistEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'prePersist';
    }
} 
