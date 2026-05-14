<?php

namespace App\Controller;

use App\Entity\IndependentAgeAction;
use App\Form\IndependentAgeActionType;
use App\Repository\IndependentAgeActionRepository;
use App\Repository\IndependentAgeRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/independent-age')]
class IndependentAgeActionController extends AbstractController
{
    #[Route('/{recordUuid}/actions/new', name: 'app_independent_age_action_new', methods: ['GET','POST'])]
    public function new(
        string $recordUuid,
        Request $request,
        IndependentAgeRecordRepository $recordRepo,
        EntityManagerInterface $em
    ): Response {
        $record = $recordRepo->findOneByUuid($recordUuid);
        if (!$record) {
            throw $this->createNotFoundException('Record not found.');
        }

        $action = new IndependentAgeAction();
        $action->setRecord($record);

        $form = $this->createForm(IndependentAgeActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($action->getActionLocation() !== 'other') {
                $action->setActionLocationOther(null);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($action);
            $em->flush();

            $this->addFlash('success', 'Action added.');
            return $this->redirectToRoute('app_independent_age_index');
        }

        return $this->render('independent_age/action_form.html.twig', [
            'title' => 'Add Action',
            'record' => $record,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/actions/{actionUuid}/edit', name: 'app_independent_age_action_edit', methods: ['GET','POST'])]
    public function edit(
        string $actionUuid,
        Request $request,
        IndependentAgeActionRepository $actionRepo,
        EntityManagerInterface $em
    ): Response {
        $action = $actionRepo->findOneByUuid($actionUuid);
        if (!$action) {
            throw $this->createNotFoundException('Action not found.');
        }

        $form = $this->createForm(IndependentAgeActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($action->getActionLocation() !== 'other') {
                $action->setActionLocationOther(null);
            }
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Action updated.');
            return $this->redirectToRoute('app_independent_age_index');
        }

        return $this->render('independent_age/action_form.html.twig', [
            'title' => 'Edit Action',
            'record' => $action->getRecord(),
            'form' => $form->createView(),
        ]);
    }

    #[Route('/actions/{actionUuid}/delete', name: 'app_independent_age_action_delete', methods: ['POST'])]
    public function delete(
        string $actionUuid,
        Request $request,
        IndependentAgeActionRepository $actionRepo,
        EntityManagerInterface $em
    ): Response {
        $action = $actionRepo->findOneByUuid($actionUuid);
        if (!$action) {
            throw $this->createNotFoundException('Action not found.');
        }

        if (!$this->isCsrfTokenValid('delete_independent_age_action_' . $action->getUuid(), (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($action);
        $em->flush();

        $this->addFlash('success', 'Action deleted.');
        return $this->redirectToRoute('app_independent_age_index');
    }
}