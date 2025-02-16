<?php

namespace App\Controller;

use App\Entity\Supplier;
use App\Repository\SupplierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/accounting/suppliers')]
class SupplierController extends AbstractController
{
    #[Route('/', name: 'app_supplier_index', methods: ['GET'])]
    public function index(SupplierRepository $supplierRepository): Response
    {
        $suppliers = $supplierRepository->findAll();

        return $this->render('supplier/suppliers.html.twig', [
            'suppliers' => $suppliers,
        ]);
    }

    #[Route('/new', name: 'app_supplier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $supplier = new Supplier();

        if($request->isMethod('POST')) {
            $supplier->setName($request->get('name'));
            $supplier->setOrganisation($request->get('organisation'));
            $supplier->setEmail($request->get('email'));
            $supplier->setPhone($request->get('phone'));
            $supplier->setAddress($request->get('address'));

            $em->persist($supplier);
            $em->flush();

            $this->addFlash('success', 'Supplier created!');

            return $this->redirectToRoute('app_supplier_index');
        }
        return $this->render('supplier/new.html.twig', [
            'supplier' => $supplier,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_supplier_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Supplier $supplier, EntityManagerInterface $em ): Response
    {
        if ($request->isMethod('POST')) {
            $name = $request->request->get('name');
            // If "name" is not in the form data, this becomes null
            // So check for that, or make name optional in your entity

            // If name is required, do something like:
            if (null === $name) {
                $this->addFlash('error', 'Name cannot be null.');
                return $this->redirectToRoute('app_supplier_edit', ['id' => $supplier->getId()]);
            }
            $supplier->setName($request->get('name'));
            $supplier->setOrganisation($request->get('organisation'));
            $supplier->setEmail($request->get('email'));
            $supplier->setPhone($request->get('phone'));
            $supplier->setAddress($request->get('address'));

            $em->flush();
            $this->addFlash('success', 'Supplier updated!');

            return $this->redirectToRoute('app_supplier_index');
        }

        return $this->render('supplier/edit.html.twig', [
            'supplier' => $supplier,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_supplier_delete', methods: ['POST'])]
    public function delete(Request $request, Supplier $supplier, EntityManagerInterface $em): Response
    {
        $em->remove($supplier);
        $em->flush();

        $this->addFlash('success', 'Supplier deleted!');
        return $this->redirectToRoute('app_supplier_index');
    }

}