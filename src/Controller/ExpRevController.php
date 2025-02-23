<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\Revenue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Uid\Uuid;

#[Route('/api/lines')]
class ExpRevController extends AbstractController
{
    #[Route('/create-expense', name: 'api_create_expense', methods: ['POST'])]
    public function createExpense(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $draftUuidString = $data['draftUuid'] ?? null;
        if (!$draftUuidString) {
            return new JsonResponse(['error' => 'Missing draftUuid'], 400);
        }
        $draftUuid = Uuid::fromString($draftUuidString);

        $expense = new Expense();
        $expense->setDraftUuid($draftUuid);
        $expense->setDescription($data['description'] ?? '');
        $expense->setAmount($data['amount'] ?? 0);

        // Optionally set dateIncurred, supplierId, etc. if posted in $data

        $em->persist($expense);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'id' => $expense->getId(),
        ]);
    }

    #[Route('/create-revenue', name: 'api_create_revenue', methods: ['POST'])]
    public function createRevenue(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $draftUuidString = $data['draftUuid'] ?? null;
        if (!$draftUuidString) {
            return new JsonResponse(['error' => 'Missing draftUuid'], 400);
        }
        $draftUuid = Uuid::fromString($draftUuidString);

        $revenue = new Revenue();
        $revenue->setdraftUuid($draftUuid);
        $revenue->setSource($data['source'] ?? 'Unknown');
        $revenue->setAmount($data['amount'] ?? 0);

        $em->persist($revenue);
        $em->flush();

        return new JsonResponse([
            'status' => 'ok',
            'id' => $revenue->getId(),
        ]);
    }
}
