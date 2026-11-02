<?php

namespace App\Controller;

use App\Entity\HubAction;
use App\Form\HubActionType;
use App\Repository\HubActionRepository;
use App\Repository\HubRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/hub', name: 'app_hub_action_')]
class HubActionController extends AbstractController
{
    #[Route(
        '/{recordUuid}/actions/new',
        name: 'new',
        methods: ['GET', 'POST']
    )]
    public function new(
        string $recordUuid,
        Request $request,
        HubRecordRepository $recordRepo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $record = $recordRepo->findOneByUuid($recordUuid);

        if (!$record) {
            throw $this->createNotFoundException(
                'Hub record not found.'
            );
        }

        $action = new HubAction();
        $record->addAction($action);

        $form = $this->createForm(HubActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($action->actionLocation !== 'other') {
                $action->actionLocationOther = null;
            }

            $em->persist($action);
            $em->flush();

            $this->addFlash('success', 'Hub action added.');

            return $this->redirectToRoute(
                'app_hub_view',
                ['uuid' => $record->getUuid()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderActionForm(
            $form,
            $action,
            'Add Hub action'
        );
    }

    #[Route(
        '/actions/{actionUuid}/edit',
        name: 'edit',
        methods: ['GET', 'POST']
    )]
    public function edit(
        string $actionUuid,
        Request $request,
        HubActionRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $action = $repo->findOneByUuid($actionUuid);

        if (!$action) {
            throw $this->createNotFoundException(
                'Hub action not found.'
            );
        }

        $form = $this->createForm(HubActionType::class, $action);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($action->actionLocation !== 'other') {
                $action->actionLocationOther = null;
            }

            $em->flush();

            $this->addFlash('success', 'Hub action updated.');

            return $this->redirectToRoute(
                'app_hub_view',
                ['uuid' => $action->getRecord()->getUuid()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderActionForm(
            $form,
            $action,
            'Edit Hub action'
        );
    }

    #[Route(
        '/actions/{actionUuid}/delete',
        name: 'delete',
        methods: ['POST']
    )]
    public function delete(
        string $actionUuid,
        Request $request,
        HubActionRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $action = $repo->findOneByUuid($actionUuid);

        if (!$action) {
            throw $this->createNotFoundException(
                'Hub action not found.'
            );
        }

        if (!$this->isCsrfTokenValid(
            'delete_hub_action_' . $action->getUuid(),
            (string) $request->request->get('_token', '')
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.'
            );
        }

        $recordUuid = $action->getRecord()->getUuid();

        $em->remove($action);
        $em->flush();

        $this->addFlash('success', 'Hub action deleted.');

        return $this->redirectToRoute(
            'app_hub_view',
            ['uuid' => $recordUuid],
            Response::HTTP_SEE_OTHER
        );
    }

    private function renderActionForm(
        FormInterface $form,
        HubAction $action,
        string $title
    ): Response {
        $response = new Response();

        if ($form->isSubmitted()) {
            $response->setStatusCode(
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $this->render('hub/action_form.html.twig', [
            'form' => $form->createView(),
            'record' => $action->getRecord(),
            'title' => $title,
        ], $response);
    }
}