<?php

namespace App\Controller;

use App\Entity\Users;
use App\Entity\UserType;
use App\Repository\UsersRepository;
use App\Repository\UserTypeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserManagementController extends AbstractController
{
    #[Route('/admin/users', name: 'app_user_management')]
    public function index(
        UsersRepository $usersRepository,
        UserTypeRepository $userTypeRepository
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('user_management/userManagement.html.twig', [
            'users' => $usersRepository->findAll(),
            'userTypes' => $userTypeRepository->findAll()
        ]);
    }


    #[Route('/admin/users/create', name: 'app_user_create', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserTypeRepository $userTypeRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $role = $request->request->get('role');

        // Map the role to a UserType entity
        $userTypeName = $role === 'ROLE_ADMIN' ? 'Admin' : 'User';
        $userType = $userTypeRepo->findOneBy(['userType' => $userTypeName]);

        if (!$userType) {
            $this->addFlash('error', 'Unable to find matching user type for role.');
            return $this->redirectToRoute('app_user_management');
        }

        $user = new Users();
        $user->setEmail($email);
        $user->setPassword($hasher->hashPassword($user, $password));
        $user->setRoles([$role]);
        $user->setUserType($userType);

        $em->persist($user);
        $em->flush();

        $this->addFlash('success', 'User created successfully');
        return $this->redirectToRoute('app_user_management');
    }


    #[Route('/admin/users/{id}/edit', name: 'app_user_edit', methods: ['POST'])]
    public function edit(
        Request $request,
        Users $user,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        UserTypeRepository $userTypeRepo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $email = $request->request->get('email');
        $password = $request->request->get('password');
        $role = $request->request->get('role');

        // Map the selected role to a UserType entity
        $userTypeName = $role === 'ROLE_ADMIN' ? 'Admin' : 'User';
        $userType = $userTypeRepo->findOneBy(['userType' => $userTypeName]);

        if (!$userType) {
            $this->addFlash('error', 'Invalid user type for selected role.');
            return $this->redirectToRoute('app_user_management');
        }

        $user->setEmail($email);
        $user->setRoles([$role]);

        if (!empty($password)) {
            $user->setPassword($hasher->hashPassword($user, $password));
        }

        $user->setUserType($userType);

        $em->flush();

        $this->addFlash('success', 'User updated successfully');
        return $this->redirectToRoute('app_user_management');
    }

    #[Route('/admin/users/{id}/delete', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Users $user, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em->remove($user);
        $em->flush();

        $this->addFlash('success', 'User deleted');
        return $this->redirectToRoute('app_user_management');
    }
}
