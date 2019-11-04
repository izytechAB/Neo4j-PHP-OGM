<?php
/**
 * Copyright (C) 2012 Louis-Philippe Huberdeau
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 */

namespace izytechAB\Neo4j;

use Doctrine\Common\Collections\ArrayCollection;
use izytechAB\Neo4j\Query\LuceneQueryProcessor;
use InvalidArgumentException;

class Repository
{
    /**
     * @var \izytechAB\Neo4j\Meta\Entity
     */
    private $meta;

    /**
     * @var \Everyman\Neo4j\Index\NodeIndex
     */
    private $index;

    /**
     * @var \izytechAB\Neo4j\EntityManager
     */
    private $entityManager;

    /**
     * @var string
     */
    private $class;

    /**
     * @param EntityManager $entityManager
     * @param Meta\Entity $meta
     */
    public function __construct(EntityManager $entityManager, Meta\Entity $meta)
    {
        $this->entityManager = $entityManager;
        $this->class = $meta->getName();
        $this->meta = $meta;
    }

    /**
     * @magic
     * @param string $name
     * @param array $arguments
     * @throws InvalidArgumentException
     * @return ArrayCollection|\Everyman\Neo4j\Node
     */
    public function __call($name, $arguments)
    {
        if (strpos($name, 'findOneBy') === 0) {
            $property = substr($name, 9);
            $property = Meta\Reflection::singularizeProperty($property);

            if ($node = $this->getIndex()->findOne($property, $arguments[0])) {
                return $this->entityManager->load($node);
            }else{
                return NULL;
            }
        } elseif (strpos($name, 'findBy') === 0) {
            $property = $this->getSearchableProperty(substr($name, 6));

            $collection = new ArrayCollection;
            foreach ($this->getIndex()->find($property, $arguments[0]) as $node) {
                $collection->add($this->entityManager->load($node));
            }

            return $collection;
        }

        throw new InvalidArgumentException('Method name must begin with "findBy" or "findOneBy"!');
    }

    /**
     * @return \Everyman\Neo4j\Index\NodeIndex
     */
    public function getIndex()
    {
        return $this->entityManager->createIndex($this->class);
    }

    /**
     * @param string $property
     * @return string
     * @throws Exception
     */
    private function getSearchableProperty($property)
    {
        $property = Meta\Reflection::singularizeProperty($property);

        foreach ($this->meta->getIndexedProperties() as $p) {
            if (Meta\Reflection::singularizeProperty($p->getName()) == $property) {
                return $property;
            }
        }

        throw new Exception("Property $property is not indexed.");
    }

    /**
     * @api
     * @param int $id
     * @return bool|\Everyman\Neo4j\Node
     */
    public function find($id)
    {
        if (!$entity = $this->entityManager->findAny($id)) {
            return false;
        }

        if (!$entity->getEntity() instanceof $this->class) {
            return false;
        }

        return $entity;
    }

    /**
     * @api
     * @return ArrayCollection
     */
    public function findAll()
    {
        $collection = new ArrayCollection();
        foreach ($this->getIndex()->query('id:*') as $node) {
            $collection->add($this->entityManager->load($node));
        }

        return $collection;
    }

    /**
     * Finds one node by a set of criteria
     *
     * @api
     * @param array $criteria An array of search criteria
     * @return \Everyman\Neo4j\Node|null
     */
    public function findOneBy(array $criteria)
    {
        $query = $this->createQuery($criteria);

        if ($node = $this->getIndex()->queryOne($query)) {
            return $this->entityManager->load($node);
        }

        return null;
    }

    /**
     * Calls the Lucene Query Processor to build the query
     *
     * @api
     * @param array $criteria An array of search criterias
     * @throws InvalidArgumentException
     * @return string
     */
    public function createQuery(array $criteria = array())
    {
        if (!empty($criteria)) {
            $queryProcessor = new LuceneQueryProcessor();
            foreach ($criteria as $key => $value) {
                $queryProcessor->addQueryTerm($key, $value);
            }

            return $queryProcessor->getQuery();
        }
        throw new InvalidArgumentException('The criteria passed to the find** method can not be empty');
    }

    /**
     * Finds all node matching the search criteria
     *
     * @api
     * @param array $criteria An array of search criteria
     * @return ArrayCollection
     */
    public function findBy(array $criteria)
    {
        $query = $this->createQuery($criteria);
        $collection = new ArrayCollection();

        foreach ($this->getIndex()->query($query) as $node) {
            $collection->add($this->entityManager->load($node));
        }

        return $collection;
    }

    /**
     * Retrieves entity manager.
     *
     * @return \izytechAB\Neo4j\EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * Retrieves meta info.
     *
     * @return \izytechAB\Neo4j\Meta\Entity
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param null $string
     * @return Query\Gremlin
     */
    protected function createGremlinQuery($string = null)
    {
        return $this->entityManager->createGremlinQuery($string);
    }

    /**
     * @return Query\Cypher
     */
    protected function createCypherQuery()
    {
        return $this->entityManager->createCypherQuery();
    }

    /**
     * @deprecated
     * @param string $class
     * @return Repository
     */
    protected function getRepository($class)
    {
        trigger_error(
            'Function izytechAB\Neo4j\Repository::getRepository is deprecated. Use izytechAB\Neo4j\EntityManager::getRepository instead!',
            E_USER_DEPRECATED
        );

        return $this->entityManager->getRepository($class);
    }
}
