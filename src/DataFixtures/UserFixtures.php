<?php

namespace App\DataFixtures;

use App\Entity\Group;
use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends BaseFixture implements DependentFixtureInterface
{
    private $encoder;
    private $roleRepository;
    public const DEFAULT_USER = 'DEFAULT_USER';

    public function __construct(UserPasswordEncoderInterface $encoder, RoleRepository $roleRepository)
    {
        $this->encoder = $encoder;
        $this->roleRepository = $roleRepository;
    }

    /**
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function loadData(ObjectManager $manager)
    {
        $defaultUser = $this->createCustomUser('default_user@gmail.com', [], [GroupFixtures::DEFAULT_GROUP]);
        $manager->persist($defaultUser);
        $appAdminUser = $this->createCustomUser('app_admin@gmail.com', ['ROLE_APP_ADMIN']);
        $manager->persist($appAdminUser);
        $groupAdminUser = $this->createCustomUser('group_admin@gmail.com', ['ROLE_GROUP_ADMIN'], [GroupFixtures::DEFAULT_GROUP]);
        $manager->persist($groupAdminUser);
        $this->addReference(self::DEFAULT_USER, $defaultUser);

        $this->createMany(User::class, 6, function(User $user, $count) {
            $roleUser = $this->roleRepository->findOneBy(['name' => 'ROLE_USER']);
            $randomGroup = $this->getRandomReference(Group::class);
            $password = $this->encoder->encodePassword($user, '123456');

            $user->setName($this->faker->firstName)
                ->setSurname($this->faker->lastName)
                ->setEmail($this->faker->email)
                ->addRole($roleUser)
                ->addGroup($randomGroup)
                ->setPassword($password)
                ->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));
        });

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            GroupFixtures::class,
            RoleFixtures::class
        ];
    }

    /**
     * @throws \Exception
     */
    private function createCustomUser($email, $roleReferences = [], $groupReferences = [])
    {
        $roleReferences[] = 'ROLE_USER';
        $defaultUser = new User();
        $password = $this->encoder->encodePassword($defaultUser, '123456');
        $defaultUser->setName($this->faker->firstName)
            ->setSurname($this->faker->lastName)
            ->setEmail($email)
            ->setPassword($password)
            ->setCreatedAt($this->faker->dateTimeBetween('-100 days', '-1 days'));

        foreach ($roleReferences as $roleReference) {
            /** @var Role $userRole */
            $userRole = $this->getReference($roleReference);
            $defaultUser->addRole($userRole);
        }

        foreach ($groupReferences as $groupReference) {
            /** @var Group $userGroup */
            $userGroup = $this->getReference($groupReference);
            $defaultUser->addGroup($userGroup);
        }

        if (!count($defaultUser->getGroups())) {
            $randomGroup = $this->getRandomReference(Group::class);
            $defaultUser->addGroup($randomGroup);
        }

        return $defaultUser;
    }
}
