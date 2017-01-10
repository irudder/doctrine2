<?php

namespace Doctrine\Tests\ORM\Mapping;

use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\Tests\Models\DDC889\DDC889Class;

class StaticPHPMappingDriverTest extends AbstractMappingDriverTest
{
    protected function loadDriver()
    {
        return new StaticPHPDriver(__DIR__ . DIRECTORY_SEPARATOR . 'php');
    }

    /**
     * All class with static::loadMetadata are entities for php driver
     *
     * @group DDC-889
     */
    public function testinvalidEntityOrMappedSuperClassShouldMentionParentClasses()
    {
        $this->createClassMetadata(DDC889Class::class);
    }
}
