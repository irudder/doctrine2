<?php

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\DBAL\Types\Type as DBALType;
use Doctrine\ORM\Mapping\AssociationMapping;
use Doctrine\Tests\Models\Generic\BooleanModel;
use Doctrine\Tests\Models\Generic\DateTimeModel;
use Doctrine\Tests\Models\Generic\DecimalModel;
use Doctrine\Tests\Models\Generic\SerializationModel;
use Doctrine\Tests\OrmFunctionalTestCase;

class TypeTest extends OrmFunctionalTestCase
{
    protected function setUp()
    {
        $this->useModelSet('generic');
        parent::setUp();
    }

    public function testDecimal()
    {
        $decimal = new DecimalModel();
        $decimal->decimal = 0.15;
        $decimal->highScale = 0.1515;

        $this->em->persist($decimal);
        $this->em->flush();
        $this->em->clear();

        $dql = "SELECT d FROM Doctrine\Tests\Models\Generic\DecimalModel d";
        $decimal = $this->em->createQuery($dql)->getSingleResult();

        self::assertEquals(0.15, $decimal->decimal);
        self::assertEquals(0.1515, $decimal->highScale);
    }

    /**
     * @group DDC-1394
     * @return void
     */
    public function testBoolean()
    {
        $bool = new BooleanModel();
        $bool->booleanField = true;

        $this->em->persist($bool);
        $this->em->flush();
        $this->em->clear();

        $dql = "SELECT b FROM Doctrine\Tests\Models\Generic\BooleanModel b WHERE b.booleanField = true";
        $bool = $this->em->createQuery($dql)->getSingleResult();

        self::assertTrue($bool->booleanField);

        $bool->booleanField = false;

        $this->em->flush();
        $this->em->clear();

        $dql = "SELECT b FROM Doctrine\Tests\Models\Generic\BooleanModel b WHERE b.booleanField = false";
        $bool = $this->em->createQuery($dql)->getSingleResult();

        self::assertFalse($bool->booleanField);
    }

    public function testArray()
    {
        $serialize = new SerializationModel();
        $serialize->array["foo"] = "bar";
        $serialize->array["bar"] = "baz";

        $this->em->persist($serialize);
        $this->em->flush();
        $this->em->clear();

        $dql = "SELECT s FROM Doctrine\Tests\Models\Generic\SerializationModel s";
        $serialize = $this->em->createQuery($dql)->getSingleResult();

        self::assertEquals(["foo" => "bar", "bar" => "baz"], $serialize->array);
    }

    public function testObject()
    {
        $serialize = new SerializationModel();
        $serialize->object = new \stdClass();

        $this->em->persist($serialize);
        $this->em->flush();
        $this->em->clear();

        $dql = "SELECT s FROM Doctrine\Tests\Models\Generic\SerializationModel s";
        $serialize = $this->em->createQuery($dql)->getSingleResult();

        self::assertInstanceOf('stdClass', $serialize->object);
    }

    public function testDate()
    {
        $dateTime = new DateTimeModel();
        $dateTime->date = new \DateTime('2009-10-01', new \DateTimeZone('Europe/Berlin'));

        $this->em->persist($dateTime);
        $this->em->flush();
        $this->em->clear();

        $dateTimeDb = $this->em->find(DateTimeModel::class, $dateTime->id);

        self::assertInstanceOf('DateTime', $dateTimeDb->date);
        self::assertEquals('2009-10-01', $dateTimeDb->date->format('Y-m-d'));
    }

    public function testDateTime()
    {
        $dateTime = new DateTimeModel();
        $dateTime->datetime = new \DateTime('2009-10-02 20:10:52', new \DateTimeZone('Europe/Berlin'));

        $this->em->persist($dateTime);
        $this->em->flush();
        $this->em->clear();

        $dateTimeDb = $this->em->find(DateTimeModel::class, $dateTime->id);

        self::assertInstanceOf('DateTime', $dateTimeDb->datetime);
        self::assertEquals('2009-10-02 20:10:52', $dateTimeDb->datetime->format('Y-m-d H:i:s'));

        $articles = $this->em->getRepository(DateTimeModel::class)->findBy(['datetime' => new \DateTime( "now" )]);
        self::assertEquals( 0, count( $articles ) );
    }

    public function testDqlQueryBindDateTimeInstance()
    {
        $date = new \DateTime('2009-10-02 20:10:52', new \DateTimeZone('Europe/Berlin'));

        $dateTime = new DateTimeModel();
        $dateTime->datetime = $date;

        $this->em->persist($dateTime);
        $this->em->flush();
        $this->em->clear();

        $dateTimeDb = $this->em->createQuery('SELECT d FROM Doctrine\Tests\Models\Generic\DateTimeModel d WHERE d.datetime = ?1')
                                ->setParameter(1, $date, DBALType::DATETIME)
                                ->getSingleResult();
    }

    public function testDqlQueryBuilderBindDateTimeInstance()
    {
        $date = new \DateTime('2009-10-02 20:10:52', new \DateTimeZone('Europe/Berlin'));

        $dateTime = new DateTimeModel();
        $dateTime->datetime = $date;

        $this->em->persist($dateTime);
        $this->em->flush();
        $this->em->clear();

        $dateTimeDb = $this->em->createQueryBuilder()
                                 ->select('d')
                                 ->from(DateTimeModel::class, 'd')
                                 ->where('d.datetime = ?1')
                                 ->setParameter(1, $date, DBALType::DATETIME)
                                 ->getQuery()->getSingleResult();
    }

    public function testTime()
    {
        $dateTime = new DateTimeModel();
        $dateTime->time = new \DateTime('2010-01-01 19:27:20');

        $this->em->persist($dateTime);
        $this->em->flush();
        $this->em->clear();

        $dateTimeDb = $this->em->find(DateTimeModel::class, $dateTime->id);

        self::assertInstanceOf('DateTime', $dateTime->time);
        self::assertEquals('19:27:20', $dateTime->time->format('H:i:s'));
    }
}
