<?php

namespace App\Controller;

use App\Entity\HubRecord;
use App\Form\HubRecordType;
use App\Repository\HubRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/hub', name: 'app_hub_')]
class HubRecordController extends AbstractController
{
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(
        Request $request,
        HubRecordRepository $repo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $q = trim((string) $request->query->get('q', ''));
        $limit = 20;
        $total = $repo->countSearchByFullName($q);
        $pages = max(1, (int) ceil($total / $limit));

        $page = min(
            $pages,
            max(1, $request->query->getInt('page', 1))
        );

        return $this->render('hub/index.html.twig', [
            'records' => $repo->findPageSearchByFullName(
                $page,
                $limit,
                $q
            ),
            'q' => $q,
            'page' => $page,
            'pages' => $pages,
            'total' => $total,
        ]);
    }

    #[Route('/new', name: 'new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $record = new HubRecord();
        $form = $this->createForm(HubRecordType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($record);
            $em->flush();

            $this->addFlash('success', 'Hub record created.');

            return $this->redirectToRoute(
                'app_hub_view',
                ['uuid' => $record->getUuid()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderRecordForm(
            $form,
            $record,
            'New Hub record'
        );
    }

    #[Route('/{uuid}/view', name: 'view', methods: ['GET'])]
    public function view(
        string $uuid,
        HubRecordRepository $repo
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $record = $repo->findOneByUuidWithActions($uuid);

        if (!$record) {
            throw $this->createNotFoundException(
                'Hub record not found.'
            );
        }

        // A disabled form displays all saved fields and choice labels.
        $details = $this->createForm(
            HubRecordType::class,
            $record,
            [
                'disabled' => true,
                'csrf_protection' => false,
            ]
        );

        return $this->render('hub/show.html.twig', [
            'record' => $record,
            'details' => $details->createView(),
        ]);
    }

    #[Route('/{uuid}/edit', name: 'edit', methods: ['GET', 'POST'])]
    public function edit(
        string $uuid,
        Request $request,
        HubRecordRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $record = $repo->findOneByUuid($uuid);

        if (!$record) {
            throw $this->createNotFoundException(
                'Hub record not found.'
            );
        }

        $form = $this->createForm(HubRecordType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Hub record updated.');

            return $this->redirectToRoute(
                'app_hub_view',
                ['uuid' => $record->getUuid()],
                Response::HTTP_SEE_OTHER
            );
        }

        return $this->renderRecordForm(
            $form,
            $record,
            'Edit Hub record'
        );
    }

    #[Route('/{uuid}/delete', name: 'delete', methods: ['POST'])]
    public function delete(
        string $uuid,
        Request $request,
        HubRecordRepository $repo,
        EntityManagerInterface $em
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $record = $repo->findOneByUuid($uuid);

        if (!$record) {
            throw $this->createNotFoundException(
                'Hub record not found.'
            );
        }

        if (!$this->isCsrfTokenValid(
            'delete_hub_record_' . $record->getUuid(),
            (string) $request->request->get('_token', '')
        )) {
            throw $this->createAccessDeniedException(
                'Invalid CSRF token.'
            );
        }

        // Only this Hub record and its Hub actions are deleted.
        $em->remove($record);
        $em->flush();

        $this->addFlash(
            'success',
            'Hub record and its actions deleted.'
        );

        return $this->redirectToRoute(
            'app_hub_index',
            [],
            Response::HTTP_SEE_OTHER
        );
    }

    private function renderRecordForm(
        FormInterface $form,
        HubRecord $record,
        string $title
    ): Response {
        $response = new Response();

        if ($form->isSubmitted()) {
            $response->setStatusCode(
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return $this->render('hub/record_form.html.twig', [
            'form' => $form->createView(),
            'record' => $record,
            'title' => $title,
        ], $response);
    }
}