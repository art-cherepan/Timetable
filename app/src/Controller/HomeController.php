<?php


namespace App\Controller;

use App\Entity\Employee;
use App\Entity\EmployeeSchedule;
use App\Entity\Schedule;
use App\Repository\EmployeeRepository;
use App\Repository\ScheduleRepository;

use App\services\ScheduleService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends BaseController
{
    protected $scheduleService;
    protected $holidays;

    /**
     * HomeController constructor.
     */
    public function __construct()
    {
        $this->scheduleService = new ScheduleService();
    }

    /**
     * @Route("/", name="home")
     */
    public function index()
    {
        $forRender = parent::renderDefault();
        return $this->render('main/index.html.twig', $forRender);
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @return Response
     * @Route("/employee-schedule", name="employee_schedule", methods={"GET"})
     */
    public function showSchedule(ManagerRegistry $doctrine, Request $request): Response
    {
        if (empty($request->query->get('employeeId'))) {
            return new Response(
                'Parameter "employeeId" is empty',
                Response::HTTP_OK
            );
        } elseif (empty($request->query->get('startDate'))) {
            return new Response(
                'Parameter "startDate" is empty',
                Response::HTTP_OK
            );
        } elseif (empty($request->query->get('endDate')))
        {
            return new Response(
                'Parameter "endDate" is empty',
                Response::HTTP_OK
            );
        }

        $employeeId = $request->query->get('employeeId');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');
        if ($endDate < $startDate) {
            return new Response(
                'Error: The end date cannot be greater than the start date.',
                Response::HTTP_OK
            );
        }

        $scheduleRepository = $doctrine->getRepository(Schedule::class);

        if (false === $scheduleRepository->setHolidays()) {
            return new Response(
                'Bad request to API data',
                Response::HTTP_OK
            );
        }

        //получаем массив праздников
        $this->holidays = $scheduleRepository->getHolidays();
        //получаем дни без праздников
        $daysWithoutHolidays = $this->scheduleService->removeHolidays($startDate, $endDate, $this->holidays);

        if (0 == count($daysWithoutHolidays)) {
            return new Response(
                'No data to display',
                Response::HTTP_OK
            );
        }
        //получаем данные по сотруднику
        $repository = $doctrine->getRepository(Employee::class)->getSchedule($employeeId);
        //получаем рабочие дни сотрудника
        $workDays = json_decode($repository[0]['work_days'], true);
        //получаем рабочее расписание сотрудника
        $timeRanges = json_decode($repository[0]['time_ranges'], true);
        //сортируем массив по убыванию ключей чтобы start было до end
        $timeRangesSort = $this->scheduleService->krSort($timeRanges);
        //сортируем массив по возрастанию временных промежутков
        $timeRangesSort = $this->scheduleService->sortInnerArrays($timeRangesSort);

        $timeRanges = json_encode($timeRangesSort);
        //получаем дни без праздников и без выходных
        $daysWithoutWeekEnds = [];
        foreach ($daysWithoutHolidays as $day) {
            $date = explode('-', $day);
            if (true === $workDays[date('N',mktime(0,0,0, $date[1], $date[2], $date[0]))]) {
                $daysWithoutWeekEnds[] = $day;
            }
        }

        if (0 == count($daysWithoutWeekEnds)) {
            return new Response(
                'No data to display',
                Response::HTTP_OK
            );
        }

        //собираем расписание
        $schedules = [];
        foreach ($daysWithoutWeekEnds as $day) {
            $schedule = [];
            $schedule['day'] = $day;
            $schedule['timeRanges'] = $timeRanges;
            $schedules[] = $schedule;
        }

        $result['schedule'] = $schedules;
        return new Response(
            json_encode($result),
            Response::HTTP_OK
        );
    }

    /**
     * @param ManagerRegistry $doctrine
     * @param Request $request
     * @return Response
     * @Route("/employee-schedule-non-working", name="employee_non_working_schedule", methods={"GET"})
     */
    public function showNonWorkingSchedule(ManagerRegistry $doctrine, Request $request): Response
    {
        if (empty($request->query->get('employeeId'))) {
            return new Response(
                'Parameter "employeeId" is empty',
                Response::HTTP_OK
            );
        } elseif (empty($request->query->get('startDate'))) {
            return new Response(
                'Parameter "startDate" is empty',
                Response::HTTP_OK
            );
        } elseif (empty($request->query->get('endDate')))
        {
            return new Response(
                'Parameter "endDate" is empty',
                Response::HTTP_OK
            );
        }

        $employeeId = $request->query->get('employeeId');
        $startDate = $request->query->get('startDate');
        $endDate = $request->query->get('endDate');

        $repository = $doctrine->getRepository(Employee::class)->getSchedule($employeeId);

        if (0 === count($repository)) {
            return new Response(
                'no data from DB',
                Response::HTTP_OK
            );
        }

        $workDays = json_decode($repository[0]['work_days'], true);

        $timeRanges = json_decode($repository[0]['time_ranges'], true);
        //сортируем массив по убыванию ключей чтобы start было до end
        $timeRangesSort = $this->scheduleService->krSort($timeRanges);
        //сортируем массив по возрастанию временных промежутков
        $timeRangesSort = $this->scheduleService->sortInnerArrays($timeRangesSort);

        //получаем массив врменных промежутков в рабочий день
        $nonWorkingTimeRanges = [];
        $timeRange['start'] = '00:00';
        $timeRange['end'] = $timeRangesSort[0]['start'];
        $nonWorkingTimeRanges[] = $timeRange;
        for ($i = 0; $i < count($timeRangesSort) - 1; $i++) {
            $timeRange['start'] = $timeRangesSort[$i]['end'];
            $timeRange['end'] = $timeRangesSort[$i + 1]['start'];
            $nonWorkingTimeRanges[] = $timeRange;
        }
        $timeRange['start'] = $timeRangesSort[count($timeRangesSort) - 1]['end'];
        $timeRange['end'] = '23:59';
        $nonWorkingTimeRanges[] = $timeRange;

        //получаем массив праздников
        $scheduleRepository = $doctrine->getRepository(Schedule::class);
        $scheduleRepository->setHolidays();
        $this->holidays = $scheduleRepository->getHolidays();

        //итоговый массив со всеми нерабочими датами
        $nonWorkingSchedule = [];

        $currDateTimeRanges = [];
        $currDate = $startDate;
        while (date($currDate) <= date($endDate)) {
            $date = explode('-', $currDate);
            if ((false === $workDays[date('N',mktime(0,0,0, $date[1], $date[2], $date[0]))]) || true === $this->scheduleService->isHoliday($currDate, $this->holidays)) {
                $currDateTimeRanges['day'] = $currDate;
                $currDateTimeRanges['timeRanges']['start'] = '00:00';
                $currDateTimeRanges['timeRanges']['end'] = '23:59';
            } else {
                $currDateTimeRanges['day'] = $currDate;
                $currDateTimeRanges['timeRanges'] = $nonWorkingTimeRanges;
            }
            $currDateTime = new \DateTime($currDate);
            $currDateTime->add(new \DateInterval('P1D'));
            $currDate = (string)$currDateTime->format('Y-m-d');
            $nonWorkingSchedule[] = $currDateTimeRanges;
        }

        if (0 == count($nonWorkingSchedule)) {
            return new Response(
                'No data to display',
                Response::HTTP_OK
            );
        }

        return new Response(
            json_encode($nonWorkingSchedule),
            Response::HTTP_OK
        );
    }
}