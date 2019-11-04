<?php

namespace izytechAB\Neo4j\Event;

use izytechAB\Neo4j\Tests\Entity\City;
use izytechAB\Neo4j\Tests\Entity\Movie;

class PersistEventTest extends \PHPUnit_Framework_TestCase
{
    public function eventNameProvider()
    {
        return array(
            array('prePersist'),
            array('postPersist'),
            array('preRemove'),
            array('postRemove')
        );
    }

    /**
     * @dataProvider eventNameProvider
     * @param $eventName
     */
    public function testGetSet($eventName)
    {
        $className = 'izytechAB\\Neo4j\\Event\\' . ucfirst($eventName);

        $city = new City();
        $city->setName('Zurich');

        $event = new $className($city);

        $this->assertSame($event->getEntity(), $city);

        $movie = new Movie();
        $movie->setTitle('Terminator');

        $event->setEntity($movie);

        $this->assertSame($event->getEntity(), $movie);

        $this->assertSame($event->getEventName(), $eventName);
    }
}
 