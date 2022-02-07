<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpClient\HttpClient;

/**
 * @method Schedule|null find($id, $lockMode = null, $lockVersion = null)
 * @method Schedule|null findOneBy(array $criteria, array $orderBy = null)
 * @method Schedule[]    findAll()
 * @method Schedule[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ScheduleRepository extends ServiceEntityRepository
{
    protected const URL = 'https://www.googleapis.com/calendar/v3/calendars/en.russian%23holiday%40group.v.calendar.google.com/events?key=';
    protected const apiKey = 'AIzaSyD1lks3smQFRT0zzYJR1DeQB77ju_0neHo';
    protected $holidays;

    /**
     * ScheduleRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    /**
     * @return bool
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function setHolidays()
    {
        $client = HttpClient::create();
        $response = $client->request('GET', self::URL . self::apiKey);
        if (200 == $response->getStatusCode()) {
            $data = $response->toArray();
            $this->holidays = [];
            foreach ($data['items'] as $item) {
                foreach ($item as $key => $value) {
                    if ('start' == $key || 'end' == $key) {
                        $this->holidays[] = $value['date'];
                    }
                }
            }
            $this->holidays = array_unique($this->holidays);
            sort($this->holidays);
        } else {
            return false;
        }
    }

    /**
     * @return mixed
     */
    public function getHolidays()
    {
        return $this->holidays;
    }


    // /**
    //  * @return Schedule[] Returns an array of Schedule objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Schedule
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
