<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Entity\User;
use App\Entity\Group;

class AppFixtures extends Fixture
{
    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager)
    {
        // bunch of new users
        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setUsername('user'.$i);
            $user->setPassword($this->hasher->hashPassword($user, 'user'.$i));
            $manager->persist($user);
        }

        // bunch 
        $user = User::createUser('user', 'user', $this->hasher);
        $user90 = User::createUser('user90', 'user90', $this->hasher);
        $manager->persist($user);
        $manager->persist($user90);
        
        
        $g1 = new Group();
        $g1->setLeader($user90)->setMotd("eggggggggggs")->setName("Chickens");
        $manager->persist($g1);

        $g2 = new Group();
        $g2->setLeader($user90)->setMotd("love them")->setName("Bananas");
        $manager->persist($g2);

        $g3 = new Group();
        $g3->setLeader($user)->setMotd("woof")->setName("Dogs");
        $manager->persist($g3);

        $g4 = new Group();
        $g4->setLeader($user)->setMotd("Meow")->setName("Cats");
        $manager->persist($g4);


        $manager->flush();
    }
}
