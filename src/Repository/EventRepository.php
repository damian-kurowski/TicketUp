<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 *
 * @method Event|null find($id, $lockMode = null, $lockVersion = null)
 * @method Event|null findOneBy(array $criteria, array $orderBy = null)
 * @method Event[]    findAll()
 * @method Event[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventRepository extends ServiceEntityRepository
{
    /**
     * Konstruktor repozytorium.
     *
     * @param ManagerRegistry $registry Menadżer rejestru Doctrine
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * Znajduje nadchodzące wydarzenia (data rozpoczęcia >= teraz),
     * opcjonalnie filtrując po fragmencie nazwy (ignorując wielkość liter).
     * Wyniki są sortowane rosnąco według daty rozpoczęcia.
     *
     * @param string|null $query Fragment nazwy do wyszukania lub null, aby pobrać wszystkie nadchodzące.
     * @return Event[] Zwraca tablicę pasujących obiektów Event.
     */
    public function findUpcomingByName(?string $query = null): array
    {
        $now = new \DateTimeImmutable();

        $qb = $this->createQueryBuilder('e');

        $qb->andWhere('e.startDate >= :now')
            ->setParameter('now', $now);

        if ($query) {

            $qb->andWhere('LOWER(e.name) LIKE LOWER(:query)')
                ->setParameter('query', '%' . $query . '%');
        }

        $qb->orderBy('e.startDate', 'ASC');

        return $qb->getQuery()->getResult();
    }
}