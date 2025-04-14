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

            $customer = $customerRepo->findOneBy(['representative' => $data['booked_by']]);
            if (!$customer) {
                return new JsonResponse(['error' => 'Customer not found.'], 400);
            }

            $room = $roomRepo->findOneBy(['roomName' => $roomName]);
            if (!$room) {
                return new JsonResponse(['error' => 'Room not found: '.$roomName], 400);
            }

            $dates = $this->generateDates($date, $recurring, $recurrenceCount);

            $seriesId = null;
            if ($recurring !== 'none') {
                $seriesId = Uuid::v4()->toRfc4122();
            }

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
                    continue;
                }

                $booking = new Booking();
                $booking->setDate($bookingDate);
                $booking->setTimeStart($startTime);
                $booking->setTimeEnd($endTime);
                $booking->setRoom($roomName);
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

                // --- Create associated Transaction ---
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

            $em->flush();

            return new JsonResponse(['status' => 'Booking(s) and transaction(s) created.']);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
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
}
