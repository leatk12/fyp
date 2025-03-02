<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\Expense;
use App\Entity\Revenue;
use App\Entity\Supplier;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/accounting/transaction')]
class TransactionController extends AbstractController
{
    #[Route('/', name: 'app_transaction_index', methods: ['GET'])]
    public function index(TransactionRepository $transactionRepo): Response
    {
        $transactions = $transactionRepo->findAll();

        return $this->render('transaction/transaction.html.twig', [
            'transactions' => $transactions,
        ]);
    }

    #[Route('/new', name: 'app_transaction_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        // 1) Ensure we have a draftUuid in the query param. If not, create one
        $draftUuidString = $request->query->get('draftUuid');
        if (!$draftUuidString) {
            $draftUuid = Uuid::v4();
            return $this->redirectToRoute('app_transaction_new', [
                'draftUuid' => $draftUuid,
            ]);
        } else {
            $draftUuid = Uuid::fromString($draftUuidString);
        }

        // 2) Create the new Transaction object
        $transaction = new Transaction();
        $transaction->setUuid(Uuid::v4());
        $transaction->setDate(new \DateTime()); // default to "today"

        // 3) Build and handle the Symfony form
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        // 4) Retrieve all possible suppliers so we can pass them to the Twig
        $allSuppliers = $em->getRepository(Supplier::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            // NEW: set the "type" field from isExpenseOrRevenue
            if ($transaction->getIsExpenseOrRevenue() === 'EXPENSE') {
                $transaction->setType('Expense'); // never null
            } else {
                $transaction->setType('Revenue'); // never null
            }

            // Link any draft lines to this transaction
            if ($transaction->getIsExpenseOrRevenue() === 'EXPENSE') {
                $expenses = $em->getRepository(Expense::class)
                    ->findBy(['draftUuid' => $draftUuid]);
                foreach ($expenses as $exp) {
                    $exp->setTransaction($transaction);
                    $exp->setDraftUuid(null);
                }
            } elseif ($transaction->getIsExpenseOrRevenue() === 'REVENUE') {
                $revenues = $em->getRepository(Revenue::class)
                    ->findBy(['draftUuid' => $draftUuid]);
                foreach ($revenues as $rev) {
                    $rev->setDraftUuid(null);
                    $transaction->addRevenue($rev);
                }
            }

            $em->persist($transaction);
            $em->flush();

            $this->addFlash('success', 'Transaction created with lines!');

            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form->createView(),
            'draftUuid' => $draftUuid,
            'suppliers' => $allSuppliers,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Transaction $transaction, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        // Retrieve suppliers as done in the new() action
        $allSuppliers = $em->getRepository(\App\Entity\Supplier::class)->findAll();

        if ($form->isSubmitted() && $form->isValid()) {
            if ($transaction->getIsExpenseOrRevenue() === 'EXPENSE') {
                $transaction->setType('Expense');
            } else {
                $transaction->setType('Revenue');
            }
            $em->flush();
            $this->addFlash('success', 'Transaction updated!');
            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
            'suppliers' => $allSuppliers,
        ]);
    }


    #[Route('/{id}/delete', name: 'app_transaction_delete', methods: ['POST'])]
    public function delete(Request $request, Transaction $transaction, EntityManagerInterface $em): Response
    {
        $em->remove($transaction);
        $em->flush();
        $this->addFlash('success', 'Transaction deleted!');
        return $this->redirectToRoute('app_transaction_index');
    }
}
