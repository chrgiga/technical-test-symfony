<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Persistence\ObjectManager;

class RoleFixtures extends BaseFixture
{
    private $roles;

    public function __construct()
    {
        $this->roles = ['ROLE_USER', 'ROLE_ADMIN', 'ROLE_APP_ADMIN', 'ROLE_GROUP_ADMIN'];
    }

    public function loadData(ObjectManager $manager)
    {
        $this->createMany(Role::class, count($this->roles), function(Role $role, $count) {
            $role->setName($this->roles[$count])
                ->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));

            $this->addReference($role->getName(), $role);
        });

        $manager->flush();
    }
}
