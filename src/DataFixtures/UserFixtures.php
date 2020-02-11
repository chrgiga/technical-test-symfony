<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class UserFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(User::class, 6, function(User $user, $count) {
            $randomGroup = $this->getRandomReference(Group::class);

            $user->setName($this->faker->firstName)
                ->setSurname($this->faker->lastName)
                ->setEmail($this->faker->email)
                ->addGroup($randomGroup)
                ->setPassword($this->faker->password(6))
                ->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GroupFixtures::class
        ];
    }
}
