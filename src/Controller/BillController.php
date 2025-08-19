<?php

namespace App\Controller;

use Dompdf\Dompdf;
use Dompdf\Options;
use App\Repository\OrderRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class BillController extends AbstractController
{
    #[Route('/bill/{id}', name: 'app_bill')]
    public function index($id, OrderRepository $orderRepo): Response
    {
        $order = $orderRepo->find($id);

        $pdfOptions = new Options();
        $pdfOptions->set('defaultFront', 'Arial');
        $domPdf = new Dompdf($pdfOptions);
        $html = $this->renderView('bill/index.html.twig', [
            'order' => $order,
        ]);
        $domPdf->loadHtml($html);
        $domPdf->render();
        $domPdf->stream('bill-' . $order->getId() . '.pdf', [
            'Attachment' => false
        ]);

        return new Response('', 200, [
            'Content-Type' => 'application/pdf'
        ]);
    }
}
