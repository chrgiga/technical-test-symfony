<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends BaseFixture implements DependentFixtureInterface
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Event::class, 6, function(Event $event, $count) {
            $startsDateTime = $this->faker->dateTime;
            $endsDateTime = clone $startsDateTime;
            $hours = $this->faker->numberBetween(4, 24);
            $endsDateTime->format("+$hours hour");
            $randomUser = $this->getRandomReference(User::class);

            $event->setName($this->faker->title)
                ->setStartsAt($startsDateTime)
                ->setEndsAt($endsDateTime)
                ->addUserId($randomUser)
                ->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class
        ];
    }
}
