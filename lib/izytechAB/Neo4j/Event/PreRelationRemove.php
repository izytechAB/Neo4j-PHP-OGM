<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event preRelationRemove
 */
class PreRelationRemove extends RelationEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'preRelationRemove';
    }

} 
