<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;

class UserFixture extends Fixture
{
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


	public function load(ObjectManager $manager)
    {
        foreach ($this->getUserData() as [$fullname, $username, $password, $email, $roles]) {
            $user = new User();
            //$user->setFullName($fullname);
            $user->setUsername($username);
            $user->setPassword($this->passwordEncoder->encodePassword($user, $password));
            //$user->setEmail($email);
            $user->setRoles($roles);
 
            $manager->persist($user);
            $this->addReference($username, $user);
        }
 
        $manager->flush();
    }

    private function getUserData(): array
    {
        return [
            // $userData = [$fullname, $username, $password, $email, $roles];
            ['SuperAdmin', 'superadmin', '123', 'superadmin@ndsigis.edu', ['ROLE_SUPER_ADMIN']],
            ['Pierre-Philippe FADY', 'pp', '123', 'pp.fady@ndsigis.edu', ['ROLE_ADMIN']],
            ['Utilisateur', 'user', '123', 'user@ndsigis.edu', ['ROLE_USER']]
        ];
    }


}
