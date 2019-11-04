<?php

namespace izytechAB\Neo4j\Event;

/**
 * Event postRelationCreate
 */
class PostRelationCreate extends RelationEvent
{
    /**
     * {@inheritdoc}
     */
    public function getEventName()
    {
        return 'postRelationCreate';
    }

} 
