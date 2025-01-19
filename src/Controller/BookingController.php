<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\BookingRepository;
use App\Repository\RoomRepository;
use App\Repository\BookingCustomerRepository;
use App\Entity\Booking;
use App\Entity\BookingCustomer;
use Symfony\Component\Routing\Annotation\Route;

class BookingController extends AbstractController
{
    #[Route('/booking', name: 'app_booking')]
    public function index(): Response
    {
        return $this->render('booking/index.html.twig', [
            'controller_name' => 'BookingController',
        ]);
    }

    #[Route('/api/bookings', name: 'fetch_bookings', methods: ['GET'])]
    public function fetchBookings(BookingRepository $repository): JsonResponse
    {
        $bookings = $repository->findAll();

        $events = array_map(function ($booking) {
            return [
                'id' => $booking->getId(),
                'title' => $booking->getBookedBy(),
                'start' => $booking->getDate()->format('Y-m-d') . 'T' . $booking->getTimeStart()->format('H:i:s'),
                'end' => $booking->getDate()->format('Y-m-d') . 'T' . $booking->getTimeEnd()->format('H:i:s'),
                'resourceId' => $booking->getRoom(),
            ];
        }, $bookings);

        return new JsonResponse($events);
    }

    #[Route('/api/bookings', name: 'create_booking', methods: ['POST'])]
    public function createBooking(Request $request, EntityManagerInterface $em, BookingCustomerRepository $customerRepo): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validate input data
            if (!is_array($data) || !isset($data['date'], $data['time_start'], $data['time_end'], $data['room'], $data['booked_by'])) {
                return new JsonResponse(['error' => 'Invalid input. Required fields are missing.'], 400);
            }

            // Find or create customer
            $customer = $customerRepo->findOneBy(['representative' => $data['booked_by']]);
            if (!$customer && isset($data['organisation'], $data['phone'], $data['email'])) {
                $customer = new BookingCustomer();
                $customer->setOrganisation($data['organisation'] ?? null);
                $customer->setRepresentative($data['booked_by']);
                $customer->setPhone($data['phone']);
                $customer->setEmail($data['email']);
                $em->persist($customer);
            } elseif (!$customer) {
                return new JsonResponse(['error' => 'Customer not found and insufficient data to create a new one.'], 400);
            }

            // Create booking
            $booking = new Booking();
            $booking->setDate(new \DateTime($data['date']));
            $booking->setTimeStart(new \DateTime($data['time_start']));
            $booking->setTimeEnd(new \DateTime($data['time_end']));
            $booking->setRecurring($data['recurring'] ?? null);
            $booking->setRecurringException(isset($data['recurring_exception']) ? new \DateTime($data['recurring_exception']) : null);
            $booking->setRoom($data['room']);
            $booking->setBookedBy($customer->getRepresentative());
            $booking->setCustId($customer->getId());

            $em->persist($booking);
            $em->flush();

            return new JsonResponse([
                'id' => $booking->getId(),
                'date' => $booking->getDate()->format('Y-m-d'),
                'time_start' => $booking->getTimeStart()->format('H:i'),
                'time_end' => $booking->getTimeEnd()->format('H:i'),
                'room' => $booking->getRoom(),
                'booked_by' => $booking->getBookedBy(),
            ]);
        } catch (\Exception $e) {
            // Improved error handling and logging
            $this->get('logger')->error('Error creating booking: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/bookings/{id}', name: 'update_booking', methods: ['PUT'])]
    public function updateBooking(Request $request, Booking $booking, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data) || !isset($data['start'], $data['end'], $data['bookedBy'])) {
                return new JsonResponse(['error' => 'Invalid input data.'], 400);
            }

            $booking->setTimeStart(new \DateTime($data['start']));
            $booking->setTimeEnd(new \DateTime($data['end']));
            $booking->setBookedBy($data['bookedBy']);

            $em->flush();

            return new JsonResponse([
                'id' => $booking->getId(),
                'start' => $booking->getTimeStart()->format('Y-m-d\TH:i:s'),
                'end' => $booking->getTimeEnd()->format('Y-m-d\TH:i:s'),
                'bookedBy' => $booking->getBookedBy(),
            ]);
        } catch (\Exception $e) {
            $this->get('logger')->error('Error updating booking: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error.'], 500);
        }
    }

    #[Route('/api/rooms', name: 'fetch_rooms', methods: ['GET'])]
    public function fetchRooms(RoomRepository $repository): JsonResponse
    {
        $rooms = $repository->findAll();

        $resources = array_map(function ($room) {
            return [
                'id' => $room->getId(),
                'title' => $room->getRoomName(),
            ];
        }, $rooms);

        return new JsonResponse($resources);
    }

    #[Route('/api/customers', name: 'fetch_customers', methods: ['GET'])]
    public function fetchCustomers(BookingCustomerRepository $repository): JsonResponse
    {
        $customers = $repository->findAll();

        $data = array_map(function ($customer) {
            return [
                'representative' => $customer->getRepresentative(),
                'organisation' => $customer->getOrganisation(),
                'phone' => $customer->getPhone(),
                'email' => $customer->getEmail(),
            ];
        }, $customers);

        return new JsonResponse($data);
    }

    #[Route('/api/customers', name: 'add_customer', methods: ['POST'])]
    public function addCustomer(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data) || !isset($data['organisation'], $data['representative'], $data['phone'], $data['email'])) {
                return new JsonResponse(['error' => 'Invalid input data.'], 400);
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
            $this->get('logger')->error('Error adding customer: ' . $e->getMessage());
            return new JsonResponse(['error' => 'Internal Server Error.'], 500);
        }
    }
}
