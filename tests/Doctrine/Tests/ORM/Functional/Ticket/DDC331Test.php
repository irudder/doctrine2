<?php

namespace Doctrine\Tests\ORM\Functional\Ticket;

/**
 * Functional tests for the Class Table Inheritance mapping strategy.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 */
class DDC331Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp() {
        $this->useModelSet('company');
        parent::setUp();
    }

    /**
     * @group DDC-331
     */
    public function testSelectFieldOnRootEntity()
    {
        $q = $this->em->createQuery('SELECT e.name FROM Doctrine\Tests\Models\Company\CompanyEmployee e');

        self::assertSQLEquals(
            'SELECT c0_."name" AS name_0 FROM "company_employees" c1_ INNER JOIN "company_persons" c0_ ON c1_."id" = c0_."id" LEFT JOIN "company_managers" c2_ ON c1_."id" = c2_."id"',
            $q->getSQL()
        );
    }
}
