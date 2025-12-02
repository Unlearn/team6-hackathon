<?php

namespace App\Controller;

use App\Entity\Subcontractor;
use App\Entity\Trade;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/wizard', name: 'api_wizard_')]
class WizardController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator
    ) {}

    #[Route('/trades', name: 'trades', methods: ['GET'])]
    public function getTrades(): JsonResponse
    {
        $trades = $this->entityManager->getRepository(Trade::class)->findAll();
        
        $tradeList = array_map(function($trade) {
            return [
                'id' => $trade->getId(),
                'name' => $trade->getName()
            ];
        }, $trades);
        
        return $this->json([
            'success' => true,
            'trades' => $tradeList
        ]);
    }

    #[Route('/step1', name: 'step1', methods: ['POST'])]
    public function step1(Request $request): JsonResponse
    {
        // Step 1 just validates the data, doesn't save to DB
        $name = $request->request->get('name');
        $abn = $request->request->get('abn');

        if (!$name || !$abn) {
            return $this->json([
                'success' => false,
                'errors' => ['name and abn are required']
            ], 400);
        }

        // Validate ABN format
        if (!preg_match('/^\d{11}$/', $abn)) {
            return $this->json([
                'success' => false,
                'errors' => ['abn' => 'ABN must be 11 digits']
            ], 400);
        }

        // Handle logo upload to temporary storage
        $logoFilename = null;
        $logoFile = $request->files->get('logo');
        if ($logoFile) {
            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads';
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0777, true);
            }
            
            $logoFilename = uniqid() . '.' . $logoFile->guessExtension();
            $logoFile->move($uploadsDirectory, $logoFilename);
        }

        return $this->json([
            'success' => true,
            'logo' => $logoFilename
        ], 200);
    }

    #[Route('/step2', name: 'step2', methods: ['POST'])]
    public function step2(Request $request): JsonResponse
    {
        // Step 2 just validates the data, doesn't save to DB
        $data = json_decode($request->getContent(), true);

        if (!isset($data['mobile']) || !isset($data['email'])) {
            return $this->json([
                'success' => false,
                'errors' => ['mobile and email are required']
            ], 400);
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'success' => false,
                'errors' => ['email' => 'Please enter a valid email address']
            ], 400);
        }

        return $this->json([
            'success' => true
        ], 200);
    }

    #[Route('/step3', name: 'step3', methods: ['POST'])]
    public function step3(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Validate employees array
        if (!isset($data['employees']) || !is_array($data['employees'])) {
            return $this->json([
                'success' => false,
                'errors' => ['employees must be an array']
            ], 400);
        }

        // Validate each employee has a name
        foreach ($data['employees'] as $employee) {
            if (empty($employee)) {
                return $this->json([
                    'success' => false,
                    'errors' => ['All employee names must be filled']
                ], 400);
            }
        }

        return $this->json([
            'success' => true
        ], 200);
    }

    #[Route('/step4', name: 'step4', methods: ['POST'])]
    public function step4(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Just validate that trades are selected
        if (!isset($data['tradeIds']) || empty($data['tradeIds'])) {
            return $this->json([
                'success' => false,
                'errors' => ['At least one trade must be selected']
            ], 400);
        }

        // Validate trade IDs exist
        foreach ($data['tradeIds'] as $tradeId) {
            $trade = $this->entityManager->getRepository(Trade::class)->find($tradeId);
            if (!$trade) {
                return $this->json([
                    'success' => false,
                    'errors' => ['Invalid trade ID: ' . $tradeId]
                ], 400);
            }
        }

        return $this->json([
            'success' => true
        ], 200);
    }

    #[Route('/step5', name: 'step5', methods: ['POST'])]
    public function step5(Request $request): JsonResponse
    {
        // Get all data from request
        $name = $request->request->get('name');
        $abn = $request->request->get('abn');
        $logo = $request->request->get('logo');
        $mobile = $request->request->get('mobile');
        $email = $request->request->get('email');
        $employees = json_decode($request->request->get('employees'), true);
        $tradeIds = json_decode($request->request->get('tradeIds'), true);

        // Validate required fields
        if (!$name || !$abn || !$mobile || !$email || !$tradeIds) {
            return $this->json([
                'success' => false,
                'errors' => ['All fields from previous steps are required']
            ], 400);
        }

        // Create subcontractor
        $subcontractor = new Subcontractor();
        $subcontractor->setName($name);
        $subcontractor->setAbn($abn);
        $subcontractor->setMobile($mobile);
        $subcontractor->setEmail($email);
        $subcontractor->setCurrentStep(5);

        if ($logo) {
            $subcontractor->setLogo($logo);
        }

        if ($employees) {
            $subcontractor->setEmployees($employees);
        }

        // Add trades
        foreach ($tradeIds as $tradeId) {
            $trade = $this->entityManager->getRepository(Trade::class)->find($tradeId);
            if ($trade) {
                $subcontractor->addTrade($trade);
            }
        }

        // Handle document uploads
        $documents = [];
        $files = $request->files->get('documents', []);
        
        if (!empty($files)) {
            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/documents';
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0777, true);
            }

            foreach ($files as $file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($uploadsDirectory, $filename);
                $documents[] = [
                    'filename' => $filename,
                    'originalName' => $file->getClientOriginalName()
                ];
            }
        }

        $subcontractor->setDocuments($documents);

        // Validate
        $errors = $this->validator->validate($subcontractor);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            
            return $this->json([
                'success' => false,
                'errors' => $errorMessages
            ], 400);
        }

        $this->entityManager->persist($subcontractor);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'id' => $subcontractor->getId(),
            'message' => 'Subcontractor created successfully'
        ], 201);
    }

}
