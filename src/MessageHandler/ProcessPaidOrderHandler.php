<?php

namespace App\MessageHandler;

use App\Entity\Order;
use App\Entity\Ticket;
use App\Message\ProcessPaidOrder;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf as PdfGenerator;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Address;
use Twig\Environment as TwigEnvironment;

#[AsMessageHandler]
final class ProcessPaidOrderHandler
{
    private EntityManagerInterface $em;
    private OrderRepository $orderRepository;
    private PdfGenerator $pdfGenerator;
    private MailerInterface $mailer;
    private LoggerInterface $logger;
    private TwigEnvironment $twig;
    private string $mailerFromAddress;
    private string $mailerFromName;

    public function __construct(
        EntityManagerInterface $em,
        OrderRepository $orderRepository,
        PdfGenerator $pdfGenerator,
        MailerInterface $mailer,
        LoggerInterface $logger,
        TwigEnvironment $twig,
        string $mailerFromAddress = 'no-reply@ticketup.local',
        string $mailerFromName = 'TicketUp System'
    ) {
        $this->em = $em;
        $this->orderRepository = $orderRepository;
        $this->pdfGenerator = $pdfGenerator;
        $this->mailer = $mailer;
        $this->logger = $logger;
        $this->twig = $twig;
        $this->mailerFromAddress = $mailerFromAddress;
        $this->mailerFromName = $mailerFromName;
    }

    public function __invoke(ProcessPaidOrder $message)
    {
        $order = $this->orderRepository->find($message->getOrderId());

        if (!$order || $order->getStatus() !== 'paid') { return; }
        if (!$order->getOrderItems()->isEmpty() && !$order->getOrderItems()->first()->getTickets()->isEmpty()) { return; }

        $this->logger->info('Processing paid order to generate tickets.', ['order_id' => $order->getId()]);
        $generatedTicketsData = [];
        $this->em->beginTransaction();

        try {
            foreach ($order->getOrderItems() as $orderItem) {
                for ($i = 0; $i < $orderItem->getQuantity(); $i++) {
                    $ticket = new Ticket();
                    $ticket->setOrderItem($orderItem);
                    $ticket->setTicketType($orderItem->getTicketType());
                    $ticket->setCode('TKT-' . strtoupper(uniqid('', true)) . '-' . $order->getId() . $i);
                    $ticket->setStatus('valid');
                    $this->em->persist($ticket);

                    $generatedTicketsData[] = [
                        'code' => $ticket->getCode(),
                        'event_name' => $orderItem->getTicketType()->getEvent()->getName(),
                        'ticket_type_name' => $orderItem->getTicketType()->getName(),
                        'event_date' => $orderItem->getTicketType()->getEvent()->getStartDate(),
                        'location' => $orderItem->getTicketType()->getEvent()->getLocationName(),
                        'price' => $orderItem->getUnitPrice(),
                    ];
                }
            }
            $this->em->flush();
            $this->em->commit();
            $this->logger->info('Tickets generated in DB.', ['order_id' => $order->getId(), 'count' => count($generatedTicketsData)]);

            $pdfContent = $this->generatePdfForTickets($order, $generatedTicketsData);
            $this->sendTicketEmail($order, $pdfContent, $generatedTicketsData);
            $this->logger->info('Ticket email dispatch attempted.', ['order_id' => $order->getId(), 'email' => $order->getCustomerEmail()]);

        } catch (\Exception $e) {
            if ($this->em->getConnection()->isTransactionActive()) { $this->em->rollback(); }
            $this->logger->error('Error processing paid order.', ['order_id' => $order->getId(), 'exception' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            throw new \RuntimeException('Failed to process order and generate tickets. Check logs.', 0, $e);
        }
    }


    private function generatePdfForTickets(Order $order, array $ticketsData): string
    {
        $html = $this->twig->render('ticket/pdf_template.html.twig', [
            'order' => $order,
            'ticketsData' => $ticketsData,
        ]);

        try {
            return $this->pdfGenerator->getOutputFromHtml($html);
        } catch (\Exception $e) {
            $this->logger->error('PDF generation failed.', ['order_id' => $order->getId(), 'exception' => $e->getMessage()]);
            throw new \RuntimeException('Nie udało się wygenerować PDF z biletami.', 0, $e);
        }
    }

    private function sendTicketEmail(Order $order, string $pdfContent, array $ticketsData): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromAddress, $this->mailerFromName))
            ->to(new Address($order->getCustomerEmail(), trim($order->getCustomerFirstName().' '.$order->getCustomerLastName())))
            ->subject('Potwierdzenie Zamówienia i Bilety - #' . $order->getId() . ' - TicketUp')
            ->htmlTemplate('emails/ticket_confirmation.html.twig')
            ->context([ 'order' => $order, 'ticketsData' => $ticketsData, ])
            ->attach($pdfContent, sprintf('bilety-ticketup-%d.pdf', $order->getId()), 'application/pdf');

        try {
            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error('Failed to send ticket email.', ['order_id' => $order->getId(), 'exception' => $e->getMessage()]);
        }
    }
}