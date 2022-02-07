<?php

namespace App\Repository;

use App\Entity\Employee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Employee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Employee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Employee[]    findAll()
 * @method Employee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EmployeeRepository extends ServiceEntityRepository
{
    /**
     * EmployeeRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Employee::class);
    }

    /**
     * @param $idEmployee
     * @return array
     * @throws \Doctrine\DBAL\Exception
     */
    public function getSchedule($idEmployee): array
    {
        $entityManager = $this->getEntityManager();
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT s.time_ranges, e.work_days
            FROM employee e LEFT JOIN schedule s ON e.id_schedule = s.id
            WHERE e.id = :id_employee';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['id_employee' => $idEmployee]);
        return $resultSet->fetchAllAssociative();
    }

//    public function getSchedules($idEmployee, $dateStart, $dateEnd): array
//    {
//        $entityManager = $this->getEntityManager();
//        $query = $entityManager->createQuery(
//            'SELECT es
//            FROM App\Entity\EmployeeSchedule es
//            WHERE es.id_employee = :id_employee AND es.date BETWEEN :date_start AND :date_end'
//        )->setParameters(['id_employee' => $idEmployee, 'date_start' => $dateStart, 'date_end' => $dateEnd]);
//
//
//        // returns an array of EmployeeSchedule objects
//        return $query->getResult();
//    }

//        $conn = $this->getEntityManager()->getConnection();
//
//        $sql = 'SELECT s.time_range, es.date FROM `employee_schedule` es
//                LEFT JOIN schedule s
//                ON es.id_schedule_id = s.id
//                WHERE es.id_employee_id = :id_employee AND
//                es.date BETWEEN :date_start AND :date_end;';
//
//        $stmt = $conn->prepare($sql);
//        $resultSet = $stmt->executeQuery(['id_employee' => $idEmployee, 'date_start' => $dateStart, 'date_end' => $dateEnd]);
//
//        // returns an array of arrays (i.e. a raw data set)
//        return $resultSet->fetchAllAssociative();


//        $conn = $this->getEntityManager()->getConnection();
//
//        $sql = 'SELECT s.time_range, es.date FROM `employee_schedule` es
//                LEFT JOIN schedule s
//                ON es.id_schedule_id = s.id
//                WHERE es.id_employee_id = :id_employee AND
//                es.date BETWEEN :date_start AND :date_end;';
//
//        $stmt = $conn->prepare($sql);
//        $resultSet = $stmt->executeQuery(['id_employee' => $idEmployee, 'date_start' => $dateStart, 'date_end' => $dateEnd]);
//
//        // returns an array of arrays (i.e. a raw data set)
//        return $resultSet->fetchAllAssociative();

    // /**
    //  * @return Employee[] Returns an array of Employee objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Employee
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
