<?php

namespace App\Controller;

use App\Repository\BookingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ReportController extends AbstractController
{
    /**
     * Render the report page (date range -> list of bookings).
     */
    #[Route('/bookings/report', name: 'app_booking_report', methods: ['GET'])]
    public function reportPage(): Response
    {
        // Restrict to admins
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('booking/report_bookings.html.twig');
    }

    /**
     * API: return bookings within [start, end] inclusive (YYYY-MM-DD).
     */
    #[Route('/api/bookings/range', name: 'api_bookings_range', methods: ['GET'])]
    public function bookingsInRange(Request $request, BookingRepository $repo): JsonResponse
    {
        // Restrict to admins
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $startStr = $request->query->get('start');
        $endStr   = $request->query->get('end');

        if (!$startStr || !$endStr) {
            return new JsonResponse(['error' => 'Missing start or end'], 400);
        }

        try {
            $start = new \DateTimeImmutable($startStr);
            $end   = new \DateTimeImmutable($endStr);
        } catch (\Throwable $e) {
            return new JsonResponse(['error' => 'Invalid date format, expected YYYY-MM-DD'], 400);
        }

        if ($end < $start) {
            return new JsonResponse(['error' => 'End date must be on or after start date'], 400);
        }

        $bookings = $repo->findBetweenDates($start, $end);

        $data = array_map(static function ($b) {
            /** @var \App\Entity\Booking $b */
            return [
                'id'         => $b->getId(),
                'date'       => $b->getDate()->format('Y-m-d'),
                'time_start' => $b->getTimeStart()->format('H:i'),
                'time_end'   => $b->getTimeEnd()->format('H:i'),
                'room'       => $b->getRoom(),
                'booked_by'  => $b->getBookedBy(),
                'cost'       => $b->getCost(),
            ];
        }, $bookings);

        return new JsonResponse($data);
    }
}
