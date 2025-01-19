<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\App\Repository\BookingRepository;

final class BookingController extends AbstractController{
    #[Route('/booking', name: 'app_booking')]
    public function index(): Response
    {
        return $this->render('booking/index.html.twig', [
            'controller_name' => 'BookingController',
        ]);
    }

    public function fetchBookings(BookingRepository $repository): JsonResponse
    {
        $bookings = $repository->findAll();

        $events = array_map(function ($booking) {
            return [
                'id' => $booking->getId(),
                'title' => $booking->getBookedBy(),
                'start' => $booking->getStartTime()->format('Y-m-d\TH:i:s'),
                'end' => $booking->getEndTime()->format('Y-m-d\TH:i:s'),
                'room' => $booking->getRoom()->getName(),
            ];
        }, $bookings);

        return new JsonResponse($events);
    }
}
