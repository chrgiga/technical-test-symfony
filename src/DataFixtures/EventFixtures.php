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
        for ($i=0; $i<10; $i++) {
            $event = $this->addEventsToDefaultUser();

            if ($event) {
                $manager->persist($event);;
            }
        }

        $this->createMany(Event::class, 20, function(Event $event, $count) {
            $startsDateTime = $this->faker->dateTimeBetween('-1 month', '1 month');
            $endsDateTime = clone $startsDateTime;
            $hours = $this->faker->numberBetween(4, 24);
            $endsDateTime->modify("+$hours hour");
            $randomUser = $this->getRandomReference(User::class);

            $event->setName($this->faker->text(25))
                ->setStartsAt($startsDateTime)
                ->setEndsAt($endsDateTime)
                ->addUser($randomUser)
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

    private function addEventsToDefaultUser()
    {
        /** @var User $user */
        $user = $this->getReference(UserFixtures::DEFAULT_USER);

        if (!$user) {
            return false;
        }

        $startsDateTime = $this->faker->dateTimeBetween('-1 month', '1 month');
        $endsDateTime = clone $startsDateTime;
        $hours = $this->faker->numberBetween(4, 24);
        $endsDateTime->modify("+$hours hour");

        $event = new Event();

        $event->setName($this->faker->text(25))
            ->setStartsAt($startsDateTime)
            ->setEndsAt($endsDateTime)
            ->addUser($user)
            ->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));

        return $event;
    }
}
