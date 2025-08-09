<?php

namespace App\Controller;

// Import necessary classes and components
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

//
#[Route('/accounting/customers')]
class CustomerController extends AbstractController
{

    /**
     * @param CustomerRepository $customerRepository
     * @return Response
     *
     * Display a list of all customers
     */
    #[Route('/', name: 'app_customer_index', methods: ['GET'])]
    public function index(CustomerRepository $customerRepository): Response
    {
        $customers = $customerRepository->findAll();

        return $this->render('customer/customers.html.twig', [
            'customers' => $customers,
        ]);
    }

    /**
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     *
     * Handles the creation of new customers
     * Supports both rendering the form and processing the POST request
     */
    #[Route('/new', name: 'app_customer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $customer = new Customer();

        if($request->isMethod('POST')) {
            // Populate the customer entity with form data
            $customer->setName($request->request->get('name'));
            $customer->setOrganisation($request->request->get('organisation'));
            $customer->setEmail($request->request->get('email'));
            $customer->setPhone($request->request->get('phone'));
            $customer->setAddress($request->request->get('address'));

            //Save the new customer
            $em->persist($customer);
            $em->flush();

            $this->addFlash('success', 'Customer created!');

            return $this->redirectToRoute('app_customer_index');
        }
        //Render the "new customer" form view
        return $this->render('customer/new.html.twig', []);
    }


    /**
     * @param Request $request
     * @param Customer $customer
     * @param EntityManagerInterface $em
     * @return Response
     *
     * Handles editing an existing customer
     * Supports bboth displaying the edit form and processing updates
     */
    #[Route('/{id}/edit', name: 'app_customer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customer $customer, EntityManagerInterface $em): Response
    {
        // if "name" is missing, reject the update
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            // If "name" is not in the form data, this becomes null


            // If name is required:
            if (null === $name) {
                $this->addFlash('error', 'Name cannot be null.');
                return $this->redirectToRoute('app_customer_edit', ['id' => $customer->getId()]);
            }
            //Update the customer fields with form data
            $customer->setName($request->request->get('name'));
            $customer->setOrganisation($request->request->get('organisation'));
            $customer->setEmail($request->request->get('email'));
            $customer->setPhone($request->request->get('phone'));
            $customer->setAddress($request->request->get('address'));

            //Persist the updated customer
            $em->flush();
            $this->addFlash('success', 'Customer updated!');

            return $this->redirectToRoute('app_customer_index');
        }
        //Render the edit form view
        return $this->render('customer/edit.html.twig', [
            'customer' => $customer,
        ]);
    }

    /**
     * @param Request $request
     * @param Customer $customer
     * @param EntityManagerInterface $em
     * @return Response
     *
     * Handles customer deletion
     * Only accessible via POST for security reasonss
     */
    #[Route('/{id}/delete', name: 'app_customer_delete', methods: ['POST'])]
    public function delete(Request $request, Customer $customer, EntityManagerInterface $em): Response
    {
        $em->remove($customer);
        $em->flush();

        $this->addFlash('success', 'Customer deleted!');
        return $this->redirectToRoute('app_customer_index');
    }
}