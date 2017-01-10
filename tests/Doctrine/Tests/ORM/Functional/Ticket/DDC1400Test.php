<?php

namespace Doctrine\Tests\ORM\Functional\Ticket;

/**
 * @group DDC-1400
 */
class DDC1400Test extends \Doctrine\Tests\OrmFunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();

        try {
            $this->schemaTool->createSchema(
                [
                $this->em->getClassMetadata(DDC1400Article::class),
                $this->em->getClassMetadata(DDC1400User::class),
                $this->em->getClassMetadata(DDC1400UserState::class),
                ]
            );
        } catch (\Exception $ignored) {
        }
    }

    public function testFailingCase()
    {
        $article = new DDC1400Article;
        $user1 = new DDC1400User;
        $user2 = new DDC1400User;

        $this->em->persist($article);
        $this->em->persist($user1);
        $this->em->persist($user2);
        $this->em->flush();

        $userState1 = new DDC1400UserState;
        $userState1->article = $article;
        $userState1->articleId = $article->id;
        $userState1->user = $user1;
        $userState1->userId = $user1->id;

        $userState2 = new DDC1400UserState;
        $userState2->article = $article;
        $userState2->articleId = $article->id;
        $userState2->user = $user2;
        $userState2->userId = $user2->id;

        $this->em->persist($userState1);
        $this->em->persist($userState2);

        $this->em->flush();
        $this->em->clear();

        $user1 = $this->em->getReference(DDC1400User::class, $user1->id);

        $q = $this->em->createQuery("SELECT a, s FROM ".__NAMESPACE__."\DDC1400Article a JOIN a.userStates s WITH s.user = :activeUser");
        $q->setParameter('activeUser', $user1);
        $articles = $q->getResult();

        $this->em->flush();
    }
}

/**
 * @Entity
 */
class DDC1400Article
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;

    /**
     * @OneToMany(targetEntity="DDC1400UserState", mappedBy="article", indexBy="userId", fetch="EXTRA_LAZY")
     */
    public $userStates;
}

/**
 * @Entity
 */
class DDC1400User
{

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    public $id;

    /**
     * @OneToMany(targetEntity="DDC1400UserState", mappedBy="user", indexBy="articleId", fetch="EXTRA_LAZY")
     */
    public $userStates;
}

/**
 * @Entity
 */
class DDC1400UserState
{

    /**
      * @Id
     *  @ManyToOne(targetEntity="DDC1400Article", inversedBy="userStates")
     */
    public $article;

    /**
      * @Id
     *  @ManyToOne(targetEntity="DDC1400User", inversedBy="userStates")
     */
    public $user;

    /**
     * @Column(name="user_id", type="integer")
     */
    public $userId;

    /**
     * @Column(name="article_id", type="integer")
     */
    public $articleId;

}
