<?php

namespace App\Controller;

use App\Entity\Subcontractor;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/site', name: 'api_site_')]
class SiteController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    #[Route('/{id}', name: 'get_site', methods: ['GET'])]
    public function getSite(int $id): JsonResponse
    {
        $subcontractor = $this->entityManager->getRepository(Subcontractor::class)->find($id);

        if (!$subcontractor) {
            return $this->json([
                'success' => false,
                'error' => 'Site not found'
            ], 404);
        }

        $trades = array_map(function($trade) {
            return $trade->getName();
        }, $subcontractor->getTrades()->toArray());

        return $this->json([
            'success' => true,
            'site' => [
                'name' => $subcontractor->getName(),
                'logo' => $subcontractor->getLogo(),
                'mobile' => $subcontractor->getMobile(),
                'email' => $subcontractor->getEmail(),
                'trades' => $trades,
                'employees' => $subcontractor->getEmployees() ?? [],
                'documents' => $subcontractor->getDocuments() ?? []
            ]
        ]);
    }
}
