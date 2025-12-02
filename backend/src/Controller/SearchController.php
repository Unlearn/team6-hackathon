<?php

namespace App\Controller;

use App\Entity\Subcontractor;
use App\Entity\Trade;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/search', name: 'api_search_')]
class SearchController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    /**
     * List all subcontractors with pagination.
     * Query params:
     * - page: page number (default 1)
     * - limit: results per page (default 10)
     */
    #[Route('/list', name: 'list', methods: ['GET'])]
    public function listSubcontractors(Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $repository = $this->entityManager->getRepository(Subcontractor::class);
        $totalCount = $repository->count([]);

        $subcontractors = $this->entityManager->createQueryBuilder()
            ->select('s')
            ->from(Subcontractor::class, 's')
            ->orderBy('s.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $results = array_map(fn(Subcontractor $sub) => $this->formatSubcontractor($sub), $subcontractors);

        return $this->json([
            'success' => true,
            'subcontractors' => $results,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'totalPages' => (int) ceil($totalCount / $limit)
            ]
        ]);
    }

    /**
     * Search for subcontractors by name, ABN, or trade.
     * Query params:
     * - q: search query (searches name and ABN)
     * - tradeIds: comma-separated trade IDs to filter by
     * - page: page number (default 1)
     * - limit: results per page (default 10)
     */
    #[Route('/subcontractors', name: 'subcontractors', methods: ['GET'])]
    public function searchSubcontractors(Request $request): JsonResponse
    {
        $query = $request->query->get('q', '');
        $tradeIdsParam = $request->query->get('tradeIds', '');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = min(50, max(1, (int) $request->query->get('limit', 10)));
        $offset = ($page - 1) * $limit;

        $tradeIds = $tradeIdsParam ? array_map('intval', explode(',', $tradeIdsParam)) : [];

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('s')
            ->from(Subcontractor::class, 's')
            ->leftJoin('s.trades', 't');

        // Apply text search on name or ABN
        if ($query) {
            $qb->andWhere($qb->expr()->orX(
                $qb->expr()->like('LOWER(s.name)', ':query'),
                $qb->expr()->like('s.abn', ':queryExact')
            ))
            ->setParameter('query', '%' . strtolower($query) . '%')
            ->setParameter('queryExact', '%' . $query . '%');
        }

        // Apply trade filter
        if (!empty($tradeIds)) {
            $qb->andWhere('t.id IN (:tradeIds)')
                ->setParameter('tradeIds', $tradeIds);
        }

        // Get total count before pagination
        $countQb = clone $qb;
        $countQb->select('COUNT(DISTINCT s.id)');
        $totalCount = (int) $countQb->getQuery()->getSingleScalarResult();

        // Apply pagination
        $qb->distinct()
            ->orderBy('s.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        $subcontractors = $qb->getQuery()->getResult();

        $results = array_map(fn(Subcontractor $sub) => $this->formatSubcontractor($sub), $subcontractors);

        return $this->json([
            'success' => true,
            'subcontractors' => $results,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $totalCount,
                'totalPages' => (int) ceil($totalCount / $limit)
            ]
        ]);
    }

    /**
     * Format a subcontractor entity for JSON response.
     */
    private function formatSubcontractor(Subcontractor $sub): array
    {
        $trades = array_map(fn(Trade $trade) => [
            'id' => $trade->getId(),
            'name' => $trade->getName()
        ], $sub->getTrades()->toArray());

        return [
            'id' => $sub->getId(),
            'name' => $sub->getName(),
            'abn' => $sub->getAbn(),
            'mobile' => $sub->getMobile(),
            'email' => $sub->getEmail(),
            'logo' => $sub->getLogo(),
            'trades' => $trades
        ];
    }
}
