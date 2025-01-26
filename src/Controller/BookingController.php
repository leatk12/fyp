<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use App\Repository\BookingCustomerRepository;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Booking;

/**
 * This controller handles:
 * 1) Rendering the booking page (GET /booking).
 * 2) Creating bookings (POST /api/bookings) with optional recurrence.
 * 3) Fetching bookings/customers/rooms (GET /api/...).
 * 4) Adding new customers (POST /api/customers).
 */
class BookingController extends AbstractController
{
    /**
     * Displays the booking page (time slots & rooms) for a given date or today.
     *
     * @Route("/booking", name="app_booking")
     */
    #[Route('/booking', name: 'app_booking')]
    public function index(
        Request $request,
        RoomRepository $roomRepository
    ): Response {
        // Retrieve ?date=YYYY-MM-DD or default to today's date
        $date = $request->query->get('date', (new \DateTime())->format('Y-m-d'));
        $rooms = $roomRepository->findAll();

        return $this->render('booking/booking.html.twig', [
            'date'  => $date,
            'rooms' => $rooms,
        ]);
    }

    /**
     * Creates booking record(s) based on 'recurring' in the payload:
     *  - "none" (single date)
     *  - "daily", "weekly", "monthly", "yearly" (multiple).
     * Name: create_booking
     * Method: POST
     * URL: /api/bookings
     */
    #[Route('/api/bookings', name: 'create_booking', methods: ['POST'])]
    public function createBooking(
        Request $request,
        EntityManagerInterface $em,
        BookingCustomerRepository $customerRepo,
        BookingRepository $bookingRepo
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // Ensure required fields are present
            if (
                !isset($data['date']) ||
                !isset($data['time_start']) ||
                !isset($data['time_end']) ||
                !isset($data['room']) ||
                !isset($data['booked_by'])
            ) {
                return new JsonResponse(
                    ['error' => 'Invalid input. Required fields are missing.'],
                    400
                );
            }

            // Parse main booking info
            $roomName   = $data['room'];
            $startTime  = new \DateTime($data['time_start']);
            $endTime    = new \DateTime($data['time_end']);
            $date       = new \DateTime($data['date']);
            $recurring  = isset($data['recurring'])
                ? strtolower($data['recurring'])
                : 'none'; // default if not provided

            // Locate the customer record
            $customer = $customerRepo->findOneBy([
                'representative' => $data['booked_by']
            ]);
            if (!$customer) {
                return new JsonResponse(
                    ['error' => 'Customer not found.'],
                    400
                );
            }

            // Generate all relevant dates based on recurrence
            $dates = $this->generateDates($date, $recurring);

            // For each date, check conflict & create a booking if no conflict
            foreach ($dates as $bookingDate) {
                $conflicts = $bookingRepo->createQueryBuilder('b')
                    ->where('b.room = :room')
                    ->andWhere('b.date = :date')
                    ->andWhere('(b.time_start < :end AND b.time_end > :start)')
                    ->setParameter('room', $roomName)
                    ->setParameter('date', $bookingDate)
                    ->setParameter('start', $startTime)
                    ->setParameter('end', $endTime)
                    ->getQuery()
                    ->getResult();

                if (count($conflicts) > 0) {
                    // Skip insertion if there's a conflict on that date/time
                    continue;
                }

                // Create & persist booking for that date
                $booking = new Booking();
                $booking->setDate($bookingDate);
                $booking->setTimeStart($startTime);
                $booking->setTimeEnd($endTime);
                $booking->setRoom($roomName);
                $booking->setBookedBy($customer->getRepresentative());
                $booking->setCustId($customer->getId());

                $em->persist($booking);
            }

            $em->flush();

            return new JsonResponse(['status' => 'Booking(s) created.']);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Internal Server Error: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Returns all bookings as JSON (GET /api/bookings).
     * The front end typically filters by date in booking.js.
     */
    #[Route('/api/bookings', name: 'fetch_bookings', methods: ['GET'])]
    public function fetchBookings(
        BookingRepository $bookingRepository
    ): JsonResponse {
        try {
            $bookings = $bookingRepository->findAll();

            $data = array_map(function (Booking $booking) {
                return [
                    'id'         => $booking->getId(),
                    'date'       => $booking->getDate()->format('Y-m-d'),
                    'time_start' => $booking->getTimeStart()->format('H:i'),
                    'time_end'   => $booking->getTimeEnd()->format('H:i'),
                    'room_id'    => $booking->getRoom(),
                    'booked_by'  => $booking->getBookedBy(),
                ];
            }, $bookings);

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Internal Server Error: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Provides a list of rooms in JSON format (GET /api/rooms).
     */
    #[Route('/api/rooms', name: 'fetch_rooms', methods: ['GET'])]
    public function fetchRooms(RoomRepository $repository): JsonResponse
    {
        $rooms = $repository->findAll();
        $data = array_map(fn($room) => [
            'id'        => $room->getId(),
            'room_name' => $room->getRoomName(),
        ], $rooms);

        return new JsonResponse($data);
    }

    /**
     * Provides a list of customers (GET /api/customers).
     */
    #[Route('/api/customers', name: 'fetch_customers', methods: ['GET'])]
    public function fetchCustomers(
        BookingCustomerRepository $repository
    ): JsonResponse {
        $customers = $repository->findAll();
        $data = array_map(fn($customer) => [
            'representative' => $customer->getRepresentative(),
            'organisation'   => $customer->getOrganisation(),
            'phone'          => $customer->getPhone(),
            'email'          => $customer->getEmail(),
        ], $customers);

        return new JsonResponse($data);
    }

    /**
     * Creates a new customer (POST /api/customers).
     */
    #[Route('/api/customers', name: 'add_customer', methods: ['POST'])]
    public function addCustomer(
        Request $request,
        EntityManagerInterface $em
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset(
                $data['organisation'],
                $data['representative'],
                $data['phone'],
                $data['email']
            )) {
                return new JsonResponse(
                    ['error' => 'Invalid input data.'],
                    400
                );
            }

            $customer = new \App\Entity\BookingCustomer();
            $customer->setOrganisation($data['organisation']);
            $customer->setRepresentative($data['representative']);
            $customer->setPhone($data['phone']);
            $customer->setEmail($data['email']);

            $em->persist($customer);
            $em->flush();

            return new JsonResponse(['status' => 'Customer added successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Internal Server Error: ' . $e->getMessage()],
                500
            );
        }
    }

    /**
     * Generates dates for daily/weekly/monthly/yearly recurrences,
     * or single date if 'none'.
     */
    private function generateDates(\DateTime $startDate, string $recurring): array
    {
        $dates = [];

        switch ($recurring) {
            case 'daily':
                // e.g. 5 consecutive days including the first
                for ($i = 0; $i < 5; $i++) {
                    $dates[] = (clone $startDate)->modify("+{$i} day");
                }
                break;

            case 'weekly':
                // e.g. 5 weeks total
                for ($i = 0; $i < 5; $i++) {
                    $dates[] = (clone $startDate)->modify('+' . ($i * 7) . ' days');
                }
                break;

            case 'monthly':
                // e.g. 5 months total
                for ($i = 0; $i < 5; $i++) {
                    $dates[] = (clone $startDate)->modify('+' . $i . ' month');
                }
                break;

            case 'yearly':
                // e.g. 5 years total
                for ($i = 0; $i < 5; $i++) {
                    $dates[] = (clone $startDate)->modify('+' . $i . ' year');
                }
                break;

            case 'none':
            default:
                $dates[] = $startDate;
                break;
        }

        return $dates;
    }
}
