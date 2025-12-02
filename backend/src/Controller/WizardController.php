<?php

namespace App\Controller;

use App\Entity\Employee;
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


    #[Route('/step3', name: 'step3', methods: ['POST'])]
    public function step3(Request $request): JsonResponse
    {
        // Handle employee data with profile pictures
        $employeesData = $request->request->get('employees');
        
        if (!$employeesData) {
            return $this->json([
                'success' => false,
                'errors' => ['At least one employee is required']
            ], 400);
        }

        $employees = json_decode($employeesData, true);
        
        if (!is_array($employees) || empty($employees)) {
            return $this->json([
                'success' => false,
                'errors' => ['At least one employee is required']
            ], 400);
        }

        $hasMainContact = false;
        foreach ($employees as $employee) {
            if (empty($employee['name'])) {
                return $this->json([
                    'success' => false,
                    'errors' => ['All employees must have a name']
                ], 400);
            }
            
            if (isset($employee['isMainContact']) && $employee['isMainContact']) {
                $hasMainContact = true;
            }
        }

        if (!$hasMainContact) {
            return $this->json([
                'success' => false,
                'errors' => ['At least one employee must be marked as main contact']
            ], 400);
        }

        // Handle profile picture uploads
        $profilePictures = [];
        $files = $request->files->all();
        
        if (!empty($files)) {
            $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/profiles';
            if (!is_dir($uploadsDirectory)) {
                mkdir($uploadsDirectory, 0777, true);
            }

            foreach ($files as $key => $file) {
                if (preg_match('/^profilePicture_(\d+)$/', $key, $matches)) {
                    $index = $matches[1];
                    $filename = uniqid() . '.' . $file->guessExtension();
                    $file->move($uploadsDirectory, $filename);
                    $profilePictures[$index] = $filename;
                }
            }
        }

        return $this->json([
            'success' => true,
            'profilePictures' => $profilePictures
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
        $employeesData = json_decode($request->request->get('employees'), true);
        $tradeIds = json_decode($request->request->get('tradeIds'), true);

        // Validate required fields
        if (!$name || !$abn || !$employeesData || !$tradeIds) {
            return $this->json([
                'success' => false,
                'errors' => ['All fields from previous steps are required']
            ], 400);
        }

        // Validate at least one main contact
        $hasMainContact = false;
        foreach ($employeesData as $empData) {
            if (isset($empData['isMainContact']) && $empData['isMainContact']) {
                $hasMainContact = true;
                break;
            }
        }

        if (!$hasMainContact) {
            return $this->json([
                'success' => false,
                'errors' => ['At least one employee must be marked as main contact']
            ], 400);
        }

        // Get main contact for subcontractor mobile/email
        $mainContact = null;
        foreach ($employeesData as $empData) {
            if (isset($empData['isMainContact']) && $empData['isMainContact']) {
                $mainContact = $empData;
                break;
            }
        }

        // Generate unique slug from subcontractor name
        $baseSlug = $this->generateSlug($name);
        $slug = $baseSlug;
        $counter = 1;
        
        // Check if slug exists and append number if needed
        while ($this->entityManager->getRepository(Subcontractor::class)->findOneBy(['slug' => $slug])) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        // Create subcontractor
        $subcontractor = new Subcontractor();
        $subcontractor->setName($name);
        $subcontractor->setAbn($abn);
        $subcontractor->setSlug($slug);
        $subcontractor->setMobile($mainContact['mobile'] ?? null);
        $subcontractor->setEmail($mainContact['email'] ?? null);
        $subcontractor->setCurrentStep(5);

        if ($logo) {
            $subcontractor->setLogo($logo);
        }

        // Create Employee entities and track main contact
        $mainContactEmployee = null;
        foreach ($employeesData as $empData) {
            $employee = new Employee();
            $employee->setName($empData['name']);
            $employee->setJobTitle($empData['jobTitle'] ?? null);
            $employee->setMobile($empData['mobile'] ?? null);
            $employee->setEmail($empData['email'] ?? null);
            $employee->setProfilePicture($empData['profilePicture'] ?? null);
            
            $subcontractor->addEmployee($employee);
            
            // Track the main contact employee
            if (isset($empData['isMainContact']) && $empData['isMainContact']) {
                $mainContactEmployee = $employee;
            }
        }
        
        // Set the main contact employee
        if ($mainContactEmployee) {
            $subcontractor->setMainContactEmployee($mainContactEmployee);
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
            'slug' => $subcontractor->getSlug(),
            'message' => 'Subcontractor created successfully'
        ], 201);
    }

    private function generateSlug(string $text): string
    {
        // Convert to lowercase
        $slug = strtolower($text);
        
        // Replace spaces and special characters with hyphens
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
        
        return $slug;
    }

}
