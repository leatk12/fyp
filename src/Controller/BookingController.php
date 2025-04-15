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
use App\Entity\Transaction;
use App\Entity\Revenue;
use App\Entity\BookingCustomer;
use Symfony\Component\Uid\Uuid;

class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_booking')]
    public function index(Request $request, RoomRepository $roomRepository): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
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

            if (!isset($data['date'], $data['time_start'], $data['time_end'], $data['room'], $data['booked_by'])) {
                return new JsonResponse(['error' => 'Invalid input. Required fields are missing.'], 400);
            }

            $roomName  = $data['room'];
            // Create base time objects (using the provided time strings)
            $timeStartObj = new \DateTime($data['time_start']);
            $timeEndObj   = new \DateTime($data['time_end']);
            $date       = new \DateTime($data['date']);
            $recurring  = strtolower($data['recurring'] ?? 'none');
            $recurrenceCount = max(1, (int)($data['recurrence_count'] ?? 1));

            $customer = $customerRepo->findOneBy(['representative' => $data['booked_by']]);
            if (!$customer) {
                return new JsonResponse(['error' => 'Customer not found.'], 400);
            }

            $room = $roomRepo->findOneBy(['roomName' => $roomName]);
            if (!$room) {
                return new JsonResponse(['error' => 'Room not found: ' . $roomName], 400);
            }

            $dates = $this->generateDates($date, $recurring, $recurrenceCount);
            $seriesId = $recurring !== 'none' ? Uuid::v4()->toRfc4122() : null;

            $conflictOccurrences = [];
            $bookingsCreated = 0;

            foreach ($dates as $bookingDate) {
                // Combine the occurrence date with the provided time parts.
                $startDateTime = (clone $bookingDate)->setTime(
                    (int)$timeStartObj->format('H'),
                    (int)$timeStartObj->format('i')
                );
                $endDateTime = (clone $bookingDate)->setTime(
                    (int)$timeEndObj->format('H'),
                    (int)$timeEndObj->format('i')
                );

                // Use the original roomName by default.
                $finalRoomName = $roomName;
                // Check for conflicts in the chosen room.
                $conflicts = $bookingRepo->createQueryBuilder('b')
                    ->where('b.room = :room')
                    ->andWhere('b.date = :date')
                    ->andWhere('(b.time_start < :end AND b.time_end > :start)')
                    ->setParameter('room', $finalRoomName)
                    ->setParameter('date', $bookingDate)
                    ->setParameter('start', $startDateTime)
                    ->setParameter('end', $endDateTime)
                    ->getQuery()
                    ->getResult();

                // If a replacement room is provided for this specific date.
                if (isset($data['conflictReplacements']) &&
                    isset($data['conflictReplacements'][$bookingDate->format('Y-m-d')])
                ) {
                    $proposedRoom = $data['conflictReplacements'][$bookingDate->format('Y-m-d')];
                    $finalRoomName = $proposedRoom;
                    $room = $roomRepo->findOneBy(['roomName' => $finalRoomName]);
                    if (!$room) {
                        return new JsonResponse(['error' => 'Replacement room not found: ' . $finalRoomName], 400);
                    }
                    // Re-check conflict in the replacement room.
                    $conflicts = $bookingRepo->createQueryBuilder('b')
                        ->where('b.room = :room')
                        ->andWhere('b.date = :date')
                        ->andWhere('(b.time_start < :end AND b.time_end > :start)')
                        ->setParameter('room', $finalRoomName)
                        ->setParameter('date', $bookingDate)
                        ->setParameter('start', $startDateTime)
                        ->setParameter('end', $endDateTime)
                        ->getQuery()
                        ->getResult();
                }

                if (count($conflicts) > 0) {
                    $conflictOccurrences[] = [
                        'date'  => $bookingDate->format('Y-m-d'),
                        'room'  => $finalRoomName,
                        'start' => $startDateTime->format('H:i'),
                        'end'   => $endDateTime->format('H:i'),
                    ];
                    continue;
                }

                // Create booking using the correct DateTime objects.
                $booking = new Booking();
                $booking->setDate($bookingDate);
                $booking->setTimeStart($startDateTime);
                $booking->setTimeEnd($endDateTime);
                $booking->setRoom($finalRoomName);
                $booking->setBookedBy($customer->getRepresentative());
                $booking->setCustId($customer->getId());
                $booking->setRecurrenceCount($recurrenceCount);
                if ($seriesId) {
                    $booking->setSeriesId($seriesId);
                }

                $customerType = $customer->getType();
                $cost = $room->getCostForType($customerType);
                $booking->setCost($cost);

                $em->persist($booking);
                $bookingsCreated++;

                // Auto-create transaction and revenue.
                $transaction = new Transaction();
                $transaction->setUuid(Uuid::v4());
                $transaction->setDate($bookingDate);
                $transaction->setDescription('Room booking for ' . $customer->getRepresentative());
                $transaction->setAmount($cost);
                $transaction->setIsExpenseOrRevenue('REVENUE');
                $transaction->setType('Revenue');
                $transaction->setCategory('Room booking');

                $revenue = new Revenue();
                $revenue->setSource($customer->getOrganisation() ?: $customer->getRepresentative());
                $revenue->setAmount($cost);
                $revenue->setSpendingConstraints(false);
                $revenue->setTransaction($transaction);
                $transaction->addRevenue($revenue);

                $em->persist($transaction);
                $em->persist($revenue);
            }

            if (!empty($conflictOccurrences) && empty($data['force'])) {
                return new JsonResponse(
                    [
                        'warning' => 'Conflicts detected in some recurring occurrences.',
                        'conflicts' => $conflictOccurrences,
                        'bookings_created' => $bookingsCreated
                    ],
                    409
                );
            }

            $em->flush();

            return new JsonResponse(
                [
                    'status' => 'Booking(s) and transaction(s) created.',
                    'bookings_created' => $bookingsCreated,
                    'conflicts_omitted' => count($conflictOccurrences)
                ]
            );
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }


    #[Route('/api/bookings', name: 'fetch_bookings', methods: ['GET'])]
    public function fetchBookings(BookingRepository $bookingRepo): JsonResponse
    {
        $bookings = $bookingRepo->findAll();

        $data = array_map(fn(Booking $b) => [
            'id'         => $b->getId(),
            'date'       => $b->getDate()->format('Y-m-d'),
            'time_start' => $b->getTimeStart()->format('H:i'),
            'time_end'   => $b->getTimeEnd()->format('H:i'),
            'room_id'    => $b->getRoom(),
            'booked_by'  => $b->getBookedBy(),
            'recurrence_count' => $b->getRecurrenceCount(),
            'series_id'  => $b->getSeriesId(),
            'cost'       => $b->getCost(),
        ], $bookings);

        return new JsonResponse($data);
    }

    #[Route('/api/bookings/{id}', name: 'api_booking_update', methods: ['PUT'])]
    public function updateBooking(Request $request, Booking $booking, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $booking->setDate(new \DateTime($data['date'] ?? $booking->getDate()->format('Y-m-d')));
        $booking->setTimeStart(new \DateTime($data['time_start'] ?? $booking->getTimeStart()->format('H:i')));
        $booking->setTimeEnd(new \DateTime($data['time_end'] ?? $booking->getTimeEnd()->format('H:i')));
        $booking->setRoom($data['room_id'] ?? $booking->getRoom());
        $booking->setBookedBy($data['booked_by'] ?? $booking->getBookedBy());

        $em->flush();

        return new JsonResponse(['message' => 'Booking updated']);
    }

    #[Route('/api/bookings/{id}', name: 'api_booking_delete', methods: ['DELETE'])]
    public function deleteBooking(Booking $booking, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($booking);
        $em->flush();
        return new JsonResponse(['message' => 'Booking deleted']);
    }

    #[Route('/api/customers', name: 'fetch_customers', methods: ['GET'])]
    public function fetchCustomers(BookingCustomerRepository $repository): JsonResponse
    {
        $customers = $repository->findAll();
        $data = array_map(fn($c) => [
            'representative' => $c->getRepresentative(),
            'organisation' => $c->getOrganisation(),
            'phone' => $c->getPhone(),
            'email' => $c->getEmail(),
        ], $customers);

        return new JsonResponse($data);
    }

    #[Route('/api/customers', name: 'add_customer', methods: ['POST'])]
    public function addCustomer(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['organisation'], $data['representative'], $data['phone'], $data['email'])) {
            return new JsonResponse(['error' => 'Invalid input data.'], 400);
        }

        $customer = new BookingCustomer();
        $customer->setOrganisation($data['organisation']);
        $customer->setRepresentative($data['representative']);
        $customer->setPhone($data['phone']);
        $customer->setEmail($data['email']);
        $customer->setType($data['type'] ?? 'individual');

        $em->persist($customer);
        $em->flush();

        return new JsonResponse(['status' => 'Customer added successfully']);
    }

    #[Route('/api/rooms', name: 'fetch_rooms', methods: ['GET'])]
    public function fetchRooms(RoomRepository $repo): JsonResponse
    {
        $rooms = $repo->findAll();
        $data = array_map(fn($room) => [
            'id' => $room->getId(),
            'room_name' => $room->getRoomName()
        ], $rooms);

        return new JsonResponse($data);
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
                    $dates[] = (clone $startDate)->modify("+" . ($i * 7) . " days");
                }
                break;
            case 'monthly':
                for ($i = 0; $i < $count; $i++) {
                    $dates[] = (clone $startDate)->modify("+$i month");
                }
                break;
            case 'yearly':
                for ($i = 0; $i < $count; $i++) {
                    $dates[] = (clone $startDate)->modify("+$i year");
                }
                break;
            default:
                $dates[] = $startDate;
                break;
        }

        return $dates;
    }
}
