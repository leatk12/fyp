<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/accounting/customers')]
class CustomerController extends AbstractController
{

    #[Route('/', name: 'app_customer_index', methods: ['GET'])]
    public function index(CustomerRepository $customerRepository): Response
    {
        $customers = $customerRepository->findAll();

        return $this->render('customer/customers.html.twig', [
            'customers' => $customers,
        ]);
    }

    #[Route('/new', name: 'app_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $customer = new Customer();

        if($request->isMethod('POST')) {
            $customer->setName($request->request->get('name'));
            $customer->setOrganisation($request->request->get('organisation'));
            $customer->setEmail($request->request->get('email'));
            $customer->setPhone($request->request->get('phone'));
            $customer->setAddress($request->request->get('address'));

            $em->persist($customer);
            $em->flush();

            $this->addFlash('success', 'Customer created!');

            return $this->redirectToRoute('app_customer_index');
        }
        return $this->render('customer/new.html.twig', []);
    }

    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $em): Response
    {
        // Only setName(...) etc. if $request->isMethod('POST')
        // *and* you want to update the customer
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            // If "name" is not in the form data, this becomes null


            // If name is required:
            if (null === $name) {
                $this->addFlash('error', 'Name cannot be null.');
                return $this->redirectToRoute('app_customer_edit', ['id' => $customer->getId()]);
            }
            $customer->setName($request->request->get('name'));
            $customer->setOrganisation($request->request->get('organisation'));
            $customer->setEmail($request->request->get('email'));
            $customer->setPhone($request->request->get('phone'));
            $customer->setAddress($request->request->get('address'));

            $em->flush();
            $this->addFlash('success', 'Customer updated!');

            return $this->redirectToRoute('app_customer_index');
        }
        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $em): Response
    {
        $em->remove($customer);
        $em->flush();

        $this->addFlash('success', 'Customer deleted!');
        return $this->redirectToRoute('app_customer_index');
    }
}