<?php

namespace App\Controller;

use App\Entity\IndependentAgeRecord;
use App\Form\IndependentAgeRecordType;
use App\Repository\IndependentAgeRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/independent-age')]
class IndependentAgeRecordController extends AbstractController
{
    #[Route('/', name: 'app_independent_age_index', methods: ['GET'])]
    public function index(IndependentAgeRecordRepository $repo): Response
    {
        $records = $repo->findAllWithActions();

        return $this->render('independent_age/index.html.twig', [
            'records' => $records,
        ]);
    }

    #[Route('/new', name: 'app_independent_age_new', methods: ['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $record = new IndependentAgeRecord();
        $record->setDate(new \DateTime()); // default to today

        $form = $this->createForm(IndependentAgeRecordType::class, $record);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($record->isEnglishFirstLanguage() === true) {
                $record->setFirstLanguage(null);
            }
            if ($record->getHousing() !== 'other') {
                $record->setHousingOther(null);
            }
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
}
