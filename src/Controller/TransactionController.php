<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\Expense;
use App\Entity\Revenue;
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
        // 1) Ensure we have a draftUuid in the query param. If not, create one:
        $draftUuidString = $request->query->get('draftUuid');
        if (!$draftUuidString) {
            $draftUuid = Uuid::v4();
            // Redirect with ?draftUuid=... so we can store lines via AJAX
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

        if ($form->isSubmitted() && $form->isValid()) {
            // 4) Once user finalizes, link any "draft" lines to this transaction
            if ($transaction->getIsExpenseOrRevenue() === 'EXPENSE') {
                $expenses = $em->getRepository(Expense::class)
                    ->findBy(['draftUuid' => $draftUuid]);
                foreach ($expenses as $exp) {
                    $exp->setTransaction($transaction);
                    $exp->setDraftUuid(null); // Clear draft so they’re final
                }
            } elseif ($transaction->getIsExpenseOrRevenue() === 'REVENUE') {
                $revenues = $em->getRepository(Revenue::class)
                    ->findBy(['draftUuid' => $draftUuid]);
                foreach ($revenues as $rev) {
                    // If you want to link revenue lines, do so. For now, just clearing the draft
                    $rev->setDraftUuid(null);
                }
            }

            // 5) Persist the transaction
            $em->persist($transaction);
            $em->flush();

            $this->addFlash('success', 'Transaction created with lines!');

            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'form' => $form->createView(),
            'draftUuid' => $draftUuid, // Pass to Twig for JS usage
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transaction_edit', methods: ['GET','POST'])]
    public function edit(Request $request, Transaction $transaction, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Transaction updated!');
            return $this->redirectToRoute('app_transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
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
