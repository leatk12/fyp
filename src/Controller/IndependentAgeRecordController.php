<?php

namespace App\Controller;

use App\Entity\IndependentAgeRecord;
use App\Form\IndependentAgeRecordType;
use App\Repository\IndependentAgeRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/independent-age')]
class IndependentAgeRecordController extends AbstractController
{
    #[Route('/', name: 'app_independent_age_index', methods: ['GET'])]
    public function index(Request $request, IndependentAgeRecordRepository $repo): Response
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = 20;

        $total = $repo->countAllRecords();
        $pages = (int) max(1, ceil($total / $limit));

        if ($page > $pages) {
            $page = $pages;
        }

        $records = $repo->findPage($page, $limit);

        return $this->render('independent_age/index.html.twig', [
            'records' => $records,
            'page'    => $page,
            'limit'   => $limit,
            'total'   => $total,
            'pages'   => $pages,
        ]);
    }

    #[Route('/new', name: 'app_independent_age_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $record = new IndependentAgeRecord();
        $record->setDate(new \DateTime());

        $form = $this->createForm(IndependentAgeRecordType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->normaliseConditionalFields($record);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($record);
            $em->flush();

            $this->addFlash('success', 'Record saved.');
            return $this->redirectToRoute('app_independent_age_index');
        }

        return $this->render('independent_age/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{uuid}/view', name: 'app_independent_age_view', methods: ['GET'])]
    public function view(string $uuid, IndependentAgeRecordRepository $repo): Response
    {
        $record = $repo->findOneByUuidWithActions($uuid);

        if (!$record) {
            throw $this->createNotFoundException('Record not found.');
        }

        return $this->render('independent_age/_record_view.html.twig', [
            'record' => $record,
        ]);
    }

    #[Route('/{uuid}/edit', name: 'app_independent_age_edit', methods: ['GET', 'POST'])]
    public function edit(
        string $uuid,
        IndependentAgeRecordRepository $repo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $record = $repo->findOneByUuid($uuid);

        if (!$record) {
            throw $this->createNotFoundException('Record not found.');
        }

        $form = $this->createForm(IndependentAgeRecordType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->normaliseConditionalFields($record);
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Record updated.');

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse([
                    'ok' => true,
                    'redirect' => $this->generateUrl('app_independent_age_index'),
                ]);
            }

            return $this->redirectToRoute('app_independent_age_index');
        }

        if ($request->isXmlHttpRequest()) {
            return $this->render('independent_age/_record_edit.html.twig', [
                'record' => $record,
                'form' => $form->createView(),
            ]);
        }

        return $this->render('independent_age/edit.html.twig', [
            'record' => $record,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{uuid}/delete', name: 'app_independent_age_delete', methods: ['POST'])]
    public function delete(
        string $uuid,
        IndependentAgeRecordRepository $repo,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $record = $repo->findOneByUuid($uuid);

        if (!$record) {
            throw $this->createNotFoundException('Record not found.');
        }

        $token = (string) $request->request->get('_token', '');

        if (!$this->isCsrfTokenValid('delete_independent_age_record_' . $uuid, $token)) {
            throw $this->createAccessDeniedException('Invalid CSRF token.');
        }

        $em->remove($record);
        $em->flush();

        $this->addFlash('success', 'Record deleted.');
        return $this->redirectToRoute('app_independent_age_index');
    }

    private function normaliseConditionalFields(IndependentAgeRecord $record): void
    {
        if ($record->isEnglishFirstLanguage() === true) {
            $record->setFirstLanguage(null);
        }

        if ($record->getHousing() !== 'other') {
            $record->setHousingOther(null);
        }

        if ($record->getEthnicity() !== 'other_ethnic_group') {
            $record->setEthnicityOther(null);
        }
    }
}