<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event preRelationCreate
 */
class PreRelationCreate extends RelationEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'preRelationCreate';
    }

} 
