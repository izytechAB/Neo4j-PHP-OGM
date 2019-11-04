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

namespace izytechAB\Neo4j\Tests;

use Doctrine\Common\Collections\ArrayCollection;
use izytechAB\Neo4j\Tests\Entity\City;

class RepositoryTest extends TestCase
{
    public function testCreateLuceneQueryWithWordWithoutSpaces()
    {
        $repo = $this->getRepository();
        $criteria = array('fullname' => 'chris', 'lastname' => 'lord');
        $query = $repo->createQuery($criteria);
        $expectedQuery = 'fullname:chris AND lastname:lord';
        $this->assertEquals($expectedQuery, $query);
    }

    public function testCreateInvalidQueryThrowsException()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $repo = $this->getRepository();
        $repo->createQuery(array());
    }

    public function testCreateLuceneQueryWithWordWithSpaces()
    {
        $repo = $this->getRepository();
        $criteria = array('fullname' => 'angus young', 'lastname' => 'lord nelson');
        $query = $repo->createQuery($criteria);
        $expectedQuery = 'fullname:"angus young" AND lastname:"lord nelson"';
        $this->assertEquals($expectedQuery, $query);
    }

    public function testQueryWithOneTermWithoutSpaces()
    {
        $repo = $this->getRepository();
        $criteria = array('fullname' => 'angus');
        $query = $repo->createQuery($criteria);
        $expectedQuery = 'fullname:angus';
        $this->assertEquals($expectedQuery, $query);
    }

    public function testQueryWithOneTermWithSpaces()
    {
        $repo = $this->getRepository();
        $criteria = array('fullname' => 'angus young');
        $query = $repo->createQuery($criteria);
        $expectedQuery = 'fullname:"angus young"';
        $this->assertEquals($expectedQuery, $query);
    }

    public function testQueryAndReturningNode()
    {
        $uid = $this->createNodes();

        $repo = $this->getRepository();

        $movie = $repo->findOneBy(array('title' => $uid));

        $this->assertEquals($uid, $movie->getTitle());
    }

    public function testQueryWithSpacesInSearchTermAndReturningNode()
    {
        $uid = $this->createNodes();

        $repo = $this->getRepository();

        $movie = $repo->findOneBy(array('title' => 'The ' . $uid));

        $this->assertEquals('The ' . $uid, $movie->getTitle());
    }

    public function testQueryForMultipleReturningNodes()
    {
        $uid = $this->createNodes();

        $repo = $this->getRepository();

        $movies = $repo->findBy(array('title' => $uid));

        $this->assertTrue($movies instanceof ArrayCollection);

        $this->assertCount(1, $movies);
    }

    public function testQueryWithMatchMultipleAndReturnsMultiple()
    {
        $uid = $this->createNodes();

        $repo = $this->getRepository();

        $movies = $repo->findBy(array('title' => '*' . $uid));

        $this->assertTrue($movies instanceof ArrayCollection);

        $this->assertCount(2, $movies);
    }

    public function testQueryReturningNoNodes()
    {
        $uid = $this->createNodes();

        $repo = $this->getRepository();

        $movies = $repo->findBy(array('title' => $uid, 'category' => 'none'));

        $this->assertTrue($movies instanceof ArrayCollection);

        $this->assertCount(0, $movies);
    }

    public function createNodes()
    {
        $em = $this->getEntityManager();

        $entity = new Entity\Movie;
        $entity->setTitle('Return of the king');
        $entity->setCategory('long');
        $em->persist($entity);

        $uid = uniqid();

        $matrix = new Entity\Movie;
        $matrix->setTitle($uid);
        $matrix->setCategory('scifi');
        $em->persist($matrix);

        $matrix2 = new Entity\Movie;
        $matrix2->setTitle('The ' . $uid);
        $matrix->setCategory('scifi');
        $em->persist($matrix2);

        $em->flush();

        return $uid;
    }

    public function testComplexLuceneQuery()
    {
        $em = $this->getEntityManager();

        $entity = new Entity\Movie;
        $entity->setTitle('Game Of Thrones');

        $em->persist($entity);
        $em->flush();

        $repository = $this->getRepository();

        $movie = $repository->findOneBy(
            array(
                'title' => '(+*am* Of +*hron*)'
            )
        );

        $this->assertEquals($entity->getTitle(), $movie->getTitle());
    }

    public function testFindAll()
    {
        $user1 = new Entity\FindAllUser;
        $user2 = new Entity\FindAllUser;
        $user3 = new Entity\FindAllUser;

        $user1->setFirstName('Alexsey');
        $user2->setFirstName('Sergey');
        $user3->setFirstName('Anatoly');

        $em = $this->getEntityManager();

        $em->persist($user1);
        $em->persist($user2);
        $em->persist($user3);

        $em->flush();

        $users = $em->getRepository('izytechAB\Neo4j\Tests\Entity\FindAllUser')->findAll();

        foreach ($users as $user) {
            $em->remove($user);
        }

        $em->flush();

        $this->assertEquals(3, count($users));
    }

    public function testFind()
    {
        $em = $this->getEntityManager();

        $city = new City();
        $city->setName('Zurich');

        $em->persist($city);
        $em->flush();
        $em->clear();

        $id = $city->getId();

        $result = $em->getRepository('izytechAB\Neo4j\Tests\Entity\City')
            ->find($id);

        $this->assertInstanceOf('izytechAB\Neo4j\Tests\Entity\City', $result);
        $this->assertSame($city->getId(), $result->getId());
        $this->assertSame($city->getName(), $result->getName());
    }

    public function testFindOneBy()
    {
        $em = $this->getEntityManager();

        $city = new City();
        $city->setName('Zurich');

        $em->persist($city);
        $em->flush();
        $em->clear();

        $name = $city->getName();

        $result = $em->getRepository('izytechAB\Neo4j\Tests\Entity\City')
            ->findOneBy(array('name' => $name));

        $this->assertInstanceOf('izytechAB\Neo4j\Tests\Entity\City', $result);
        $this->assertSame($city->getName(), $result->getName());

        $result = $em->getRepository('izytechAB\Neo4j\Tests\Entity\City')
            ->findOneBy(array('name' => 'Does not exist!'));

        $this->assertNull($result);
    }

    public function testInvalidRepositoryCall()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $repo = $this->getRepository();

        $repo->findNothing();
    }

    public function testGetEntityManager()
    {
        $repo = $this->getRepository();
        $em = $repo->getEntityManager();

        $this->assertInstanceOf('izytechAB\Neo4j\EntityManager', $em);
    }

    public function testGetMeta()
    {
        $repo = $this->getRepository();
        $meta = $repo->getMeta();

        $this->assertInstanceOf('izytechAB\Neo4j\Meta\Entity', $meta);
    }

    /**
     * @return \izytechAB\Neo4j\Repository
     */
    private function getRepository()
    {
        $em = $this->getEntityManager();

        return $em->getRepository('izytechAB\\Neo4j\\Tests\\Entity\\Movie');
    }
}
