<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use App\Repository\BookingCustomerRepository;
use App\Entity\Booking;

// We can use Symfony's Uuid (or a custom random approach)
use Symfony\Component\Uid\Uuid;

class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_booking')]
    public function index(
        Request $request,
        RoomRepository $roomRepository
    ): JsonResponse|\Symfony\Component\HttpFoundation\Response {
        $date = $request->query->get('date', (new \DateTime())->format('Y-m-d'));
        $rooms = $roomRepository->findAll();

        return $this->render('booking/booking.html.twig', [
            'date'  => $date,
            'rooms' => $rooms,
        ]);
    }

    #[Route('/api/bookings', name: 'create_booking', methods: ['POST'])]
    public function createBooking(
        Request $request,
        EntityManagerInterface $em,
        BookingCustomerRepository $customerRepo,
        BookingRepository $bookingRepo,
        RoomRepository $roomRepo
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

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

            $roomName  = $data['room'];
            $startTime = new \DateTime($data['time_start']);
            $endTime   = new \DateTime($data['time_end']);
            $date      = new \DateTime($data['date']);
            $recurring = isset($data['recurring']) ? strtolower($data['recurring']) : 'none';
            $recurrenceCount = $data['recurrence_count'] ?? 1;
            $recurrenceCount = max(1, (int)$recurrenceCount);

            // 1) Locate the customer
            $customer = $customerRepo->findOneBy([
                'representative' => $data['booked_by']
            ]);
            if (!$customer) {
                return new JsonResponse(['error' => 'Customer not found.'], 400);
            }

            // 2) Locate the room (by roomName)
            $room = $roomRepo->findOneBy(['roomName' => $roomName]);
            if (!$room) {
                return new JsonResponse(['error' => 'Room not found: '.$roomName], 400);
            }

            // Generate the set of dates
            $dates = $this->generateDates($date, $recurring, $recurrenceCount);

            // 3) Generate a seriesId if the user selected a recurrence
            // If you'd like to always have a seriesId (even for single-booking), you can always do this.
            $seriesId = null;
            if ($recurring !== 'none') {
                // Use a random UUID or your own approach
                $seriesId = Uuid::v4()->toRfc4122();
            }

            foreach ($dates as $bookingDate) {
                // Check conflicts
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
                    // skip this date
                    continue;
                }

                // 4) Create booking
                $booking = new Booking();
                $booking->setDate($bookingDate);
                $booking->setTimeStart($startTime);
                $booking->setTimeEnd($endTime);
                $booking->setRoom($roomName);
                $booking->setBookedBy($customer->getRepresentative());
                $booking->setCustId($customer->getId());
                $booking->setRecurrenceCount($recurrenceCount);

                // 5) If we have a seriesId (recurring != none), store it
                if ($seriesId) {
                    $booking->setSeriesId($seriesId);
                }

                // 6) Calculate cost based on room cost fields + customer's type
                $customerType = $customer->getType(); // "individual","private","community"
                $cost = $room->getCostForType($customerType); // method in Room entity
                $booking->setCost($cost);

                $em->persist($booking);
            }

            $em->flush();

            return new JsonResponse(['status' => 'Booking(s) created.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/bookings', name: 'fetch_bookings', methods: ['GET'])]
    public function fetchBookings(BookingRepository $bookingRepository): JsonResponse
    {
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
                    'recurrence_count' => $booking->getRecurrenceCount(),
                    'series_id'  => $booking->getSeriesId(),
                    'cost'       => $booking->getCost(),
                ];
            }, $bookings);

            return new JsonResponse($data);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

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

    #[Route('/api/customers', name: 'fetch_customers', methods: ['GET'])]
    public function fetchCustomers(BookingCustomerRepository $repository): JsonResponse
    {
        $customers = $repository->findAll();
        $data = array_map(fn($customer) => [
            'representative' => $customer->getRepresentative(),
            'organisation'   => $customer->getOrganisation(),
            'phone'          => $customer->getPhone(),
            'email'          => $customer->getEmail(),
        ], $customers);

        return new JsonResponse($data);
    }

    #[Route('/api/customers', name: 'add_customer', methods: ['POST'])]
    public function addCustomer(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!isset($data['organisation'], $data['representative'], $data['phone'], $data['email'])) {
                return new JsonResponse(['error' => 'Invalid input data.'], 400);
            }

            $customer = new \App\Entity\BookingCustomer();
            $customer->setOrganisation($data['organisation']);
            $customer->setRepresentative($data['representative']);
            $customer->setPhone($data['phone']);
            $customer->setEmail($data['email']);

            $type = $data['type'] ?? 'individual';
            $customer->setType($type);

            $em->persist($customer);
            $em->flush();

            return new JsonResponse(['status' => 'Customer added successfully']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    private function generateDates(\DateTime $startDate, string $recurring, int $count): array
    {
        $dates = [];

        switch ($recurring) {
            case 'daily':
                for ($i = 0; $i < $count; $i++) {
                    $dates[] = (clone $startDate)->modify("+{$i} day");
                }
                break;

            case 'weekly':
                for ($i = 0; $i < $count; $i++) {
                    $dates[] = (clone $startDate)->modify('+' . ($i * 7) . ' days');
                }
                break;

            case 'monthly':
                for ($i = 0; $i < $count; $i++) {
                    $dates[] = (clone $startDate)->modify('+' . $i . ' month');
                }
                break;

            case 'yearly':
                for ($i = 0; $i < $count; $i++) {
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

    #[Route('/api/bookings/{id}', name: 'api_booking_update', methods: ['PUT'])]
    public function updateBooking(Request $request, Booking $booking, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        // optionally update fields here...
        $em->flush();

        return new JsonResponse(['message' => 'Booking updated'], 200);
    }

    #[Route('/api/bookings/{id}', name: 'api_booking_delete', methods: ['DELETE'])]
    public function deleteBooking(Booking $booking, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($booking);
        $em->flush();

        return new JsonResponse(['message' => 'Booking deleted'], 200);
    }
}
