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

class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_booking')]
    public function index(
        Request $request,
        RoomRepository $roomRepository
    ): Response {
        $date = $request->query->get('date', (new \DateTime())->format('Y-m-d'));
        $rooms = $roomRepository->findAll();

        return $this->render('booking/booking.html.twig', [
            'date' => $date,
            'rooms' => $rooms,
        ]);
    }

    #[Route('/api/bookings_no_recurrence', name: 'create_booking_no_recurrence', methods: ['POST'])]

    public function createBooking(
        Request $request,
        EntityManagerInterface $em,
        BookingCustomerRepository $customerRepo,
        BookingRepository $bookingRepo
    ): JsonResponse {
        try {
            $data = json_decode($request->getContent(), true);

            // Validate required fields
            if (
                !isset($data['date'],
                    $data['time_start'],
                    $data['time_end'],
                    $data['room'],
                    $data['booked_by'])
            ) {
                return new JsonResponse([
                    'error' => 'Invalid input. Required fields are missing.'
                ], 400);
            }

            $roomName  = $data['room'];
            $startTime = new \DateTime($data['time_start']);
            $endTime   = new \DateTime($data['time_end']);
            $date      = new \DateTime($data['date']);

            // Fetch or check the customer
            $customer = $customerRepo->findOneBy([
                'representative' => $data['booked_by']
            ]);
            if (!$customer) {
                return new JsonResponse([
                    'error' => 'Customer not found.'
                ], 400);
            }

            // Check for booking conflicts
            $conflicts = $bookingRepo->createQueryBuilder('b')
                ->where('b.room = :room')
                ->andWhere('b.date = :date')
                ->andWhere('(b.time_start < :end AND b.time_end > :start)')
                ->setParameter('room', $roomName)
                ->setParameter('date', $date)
                ->setParameter('start', $startTime)
                ->setParameter('end', $endTime)
                ->getQuery()
                ->getResult();

            if (count($conflicts) > 0) {
                return new JsonResponse([
                    'error' => 'Time slot conflict.'
                ], 409);
            }

            // Single booking, no recurrence
            $booking = new Booking();
            $booking->setDate($date);
            $booking->setTimeStart($startTime);
            $booking->setTimeEnd($endTime);
            $booking->setRoom($roomName);
            $booking->setBookedBy($customer->getRepresentative());
            $booking->setCustId($customer->getId());

            $em->persist($booking);
            $em->flush();

            return new JsonResponse(['status' => 'Booking created.']);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/api/bookings', name: 'fetch_bookings', methods: ['GET'])]
    public function fetchBookings(
        BookingRepository $bookingRepository
    ): JsonResponse {
        try {
            $bookings = $bookingRepository->findAll();

            $data = array_map(function ($booking) {
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
            return new JsonResponse([
                'error' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
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
                return new JsonResponse([
                    'error' => 'Invalid input data.'
                ], 400);
            }

            $customer = new BookingCustomer();
            $customer->setOrganisation($data['organisation']);
            $customer->setRepresentative($data['representative']);
            $customer->setPhone($data['phone']);
            $customer->setEmail($data['email']);

            $em->persist($customer);
            $em->flush();

            return new JsonResponse(['status' => 'Customer added successfully']);
        } catch (\Exception $e) {
            return new JsonResponse([
                'error' => 'Internal Server Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
