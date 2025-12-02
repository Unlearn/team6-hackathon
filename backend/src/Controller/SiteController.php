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

    #[Route('/{identifier}', name: 'get_site', methods: ['GET'])]
    public function getSite(string $identifier): JsonResponse
    {
        // Try to find by slug first, then by ID
        $subcontractor = $this->entityManager->getRepository(Subcontractor::class)->findOneBy(['slug' => $identifier]);
        
        if (!$subcontractor && is_numeric($identifier)) {
            $subcontractor = $this->entityManager->getRepository(Subcontractor::class)->find((int)$identifier);
        }

        if (!$subcontractor) {
            return $this->json([
                'success' => false,
                'error' => 'Site not found'
            ], 404);
        }

        $trades = array_map(function($trade) {
            return $trade->getName();
        }, $subcontractor->getTrades()->toArray());

        $employees = array_map(function($employee) use ($subcontractor) {
            return [
                'id' => $employee->getId(),
                'name' => $employee->getName(),
                'jobTitle' => $employee->getJobTitle(),
                'mobile' => $employee->getMobile(),
                'email' => $employee->getEmail(),
                'profilePicture' => $employee->getProfilePicture(),
                'isMainContact' => $subcontractor->getMainContactEmployee() && 
                                   $subcontractor->getMainContactEmployee()->getId() === $employee->getId()
            ];
        }, $subcontractor->getEmployees()->toArray());

        return $this->json([
            'success' => true,
            'site' => [
                'name' => $subcontractor->getName(),
                'logo' => $subcontractor->getLogo(),
                'mobile' => $subcontractor->getMobile(),
                'email' => $subcontractor->getEmail(),
                'trades' => $trades,
                'employees' => $employees,
                'documents' => $subcontractor->getDocuments() ?? []
            ]
        ]);
    }
}
