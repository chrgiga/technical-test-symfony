<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture implements DependentFixtureInterface
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function loadData(ObjectManager $manager)
    {

        $this->createMany(User::class, 6, function(User $user, $count) {
            $randomGroup = $this->getRandomReference(Group::class);
            $password = $this->encoder->encodePassword($user, '123456');

            $user->setName($this->faker->firstName)
                ->setSurname($this->faker->lastName)
                ->setEmail($this->faker->email)
                ->addGroup($randomGroup)
                ->setPassword($password)
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
