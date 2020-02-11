<?php

namespace App\DataFixtures;

use App\Entity\Group;
use Doctrine\Persistence\ObjectManager;

class GroupFixtures extends BaseFixture
{
    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Group::class, 10, function(Group $group, $count) {
            $group->setName(str_replace(' ', '_', $this->faker->colorName).' Group')
                ->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
        });

        $manager->flush();
    }
}
