<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AttendanceSeeder extends Seeder
{
    /**
     * প্রতি এমপ্লয়ির জন্য প্রতি মাসে অন্তত এই সংখ্যক দিন Late থাকবে
     */
    private const LATE_DAYS_PER_MONTH = 4;

    /**
     * Weekend ধরা হয়েছে Friday & Saturday (বাংলাদেশ অনুযায়ী)।
     * প্রয়োজন হলে নিচের অ্যারে পরিবর্তন করুন।
     */
    private const WEEKEND_DAYS = [Carbon::FRIDAY];

    public function run(): void
    {
        $startDate = Carbon::parse('2026-01-01');
        $endDate   = Carbon::parse('2026-09-02');

        $employees = Employee::all();

        if ($employees->isEmpty()) {
            $this->command->warn('কোনো Employee পাওয়া যায়নি। প্রথমে EmployeeSeeder রান করুন।');
            return;
        }

        // এই রেঞ্জের পুরনো ডেটা থাকলে মুছে ফেলা (idempotent seeder)
        Attendance::withTrashed()
            ->whereIn('employee_id', $employees->pluck('id'))
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->forceDelete();

        DB::beginTransaction();
        try {
            foreach ($employees as $employee) {
                $this->seedForEmployee($employee, $startDate->copy(), $endDate->copy());
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Attendance seeding failed: ' . $e->getMessage());
            throw $e;
        }
    }

    private function seedForEmployee(Employee $employee, Carbon $start, Carbon $end): void
    {
        $officeIn  = !empty($employee->in_time)  ? Carbon::parse($employee->in_time)->format('H:i:s')  : '09:00:00';
        $officeOut = !empty($employee->out_time) ? Carbon::parse($employee->out_time)->format('H:i:s') : '18:00:00';
        $graceTime = $employee->grace_time ?? 15;

        // মাস অনুযায়ী working day গুলো গ্রুপ করে প্রতি মাসে ৪টি random late day বাছাই
        $monthlyWorkingDays = [];
        foreach (CarbonPeriod::create($start, $end) as $date) {
            if (in_array($date->dayOfWeek, self::WEEKEND_DAYS, true)) {
                continue;
            }
            $monthlyWorkingDays[$date->format('Y-m')][] = $date->format('Y-m-d');
        }

        $lateDatesSet = [];
        foreach ($monthlyWorkingDays as $dates) {
            $count = min(self::LATE_DAYS_PER_MONTH, count($dates));
            foreach (collect($dates)->shuffle()->take($count) as $d) {
                $lateDatesSet[$d] = true;
            }
        }

        $rows = [];

        foreach (CarbonPeriod::create($start, $end) as $date) {
            $dateStr = $date->format('Y-m-d');

            if (in_array($date->dayOfWeek, self::WEEKEND_DAYS, true)) {
                $rows[] = $this->buildOffRow($employee, $dateStr, Attendance::STATUS_WEEKEND);
                continue;
            }

            $isForcedLate = isset($lateDatesSet[$dateStr]);

            // Late-day না হলে সামান্য কিছু absence (৩%)
            if (!$isForcedLate && random_int(1, 100) <= 3) {
                $rows[] = $this->buildOffRow($employee, $dateStr, Attendance::STATUS_ABSENT);
                continue;
            }

            $rows[] = $this->buildPresentRow($employee, $dateStr, $officeIn, $officeOut, $graceTime, $isForcedLate);
        }

        collect($rows)->chunk(200)->each(fn ($chunk) => Attendance::insert($chunk->all()));

        $this->command->info("Attendance seeded: {$employee->first_name} {$employee->last_name}");
    }

    private function buildPresentRow(
        Employee $employee,
        string $date,
        string $officeIn,
        string $officeOut,
        int $graceTime,
        bool $forceLate
    ): array {
        $officeInCarbon = Carbon::parse("$date $officeIn");

        if ($forceLate) {
            // grace + 16 থেকে grace + 90 মিনিট পর্যন্ত দেরি
            $lateMinutes = random_int($graceTime + 16, $graceTime + 90);
            $inTime = $officeInCarbon->copy()->addMinutes($lateMinutes);
        } else {
            // অফিসের ১০ মিনিট আগে থেকে grace এর মধ্যেই ঢোকা (on-time)
            $inTime = $officeInCarbon->copy()->addMinutes(random_int(-10, $graceTime));
        }

        $officeOutCarbon = Carbon::parse("$date $officeOut");
        $outTime = $officeOutCarbon->copy()->addMinutes(random_int(-30, 60));

        if ($outTime->lessThanOrEqualTo($inTime)) {
            $outTime = $inTime->copy()->addHours(8);
        }

        $data = [
            'company_id'  => $employee->company_id,
            'employee_id' => $employee->id,
            'date'        => $date,
            'in_time'     => $inTime->format('Y-m-d H:i:s'),
            'out_time'    => $outTime->format('Y-m-d H:i:s'),
            'grace_time'  => $graceTime,
        ];

        $data = $this->calculateAttendanceMetrics($data, $officeIn, $officeOut);

        $data['status'] = $data['is_late']
            ? Attendance::STATUS_LATE
            : (($data['is_early_out'] ?? false) ? Attendance::STATUS_EARLY_OUT : Attendance::STATUS_PRESENT);

        $data['created_at'] = now();
        $data['updated_at'] = now();

        return $data;
    }

    private function buildOffRow(Employee $employee, string $date, int $status): array
    {
        return [
            'company_id'    => $employee->company_id,
            'employee_id'   => $employee->id,
            'date'          => $date,
            'in_time'       => null,
            'out_time'      => null,
            'grace_time'    => null,
            'late_time'     => null,
            'over_time'     => null,
            'working_hours' => null,
            'is_late'       => false,
            'is_early_out'  => false,
            'status'        => $status,
            'created_at'    => now(),
            'updated_at'    => now(),
        ];
    }

    /**
     * AttendanceService@calculateAttendanceMetrics এর মতোই হিসাব (seeder-এ ডুপ্লিকেট রাখা হয়েছে,
     * কারণ seeder context-এ actual service ক্লাস inject করতে গেলে DI/company-scope জটিলতা হতে পারে)
     */
    private function calculateAttendanceMetrics(array $data, string $officeInRaw, string $officeOutRaw): array
    {
        $in  = !empty($data['in_time'])  ? Carbon::parse($data['in_time'])  : null;
        $out = !empty($data['out_time']) ? Carbon::parse($data['out_time']) : null;

        if ($in) {
            $officeIn = Carbon::parse($data['date'] . ' ' . $officeInRaw);
            $grace    = $data['grace_time'] ?? 15;

            if ($in->greaterThan($officeIn->copy()->addMinutes($grace))) {
                $data['is_late']   = true;
                $data['late_time'] = $in->diff($officeIn)->format('%H:%I');
            } else {
                $data['is_late']   = false;
                $data['late_time'] = null;
            }
        }

        if ($in && $out) {
            $data['working_hours'] = $out->diff($in)->format('%H:%I');

            $officeOut = Carbon::parse($data['date'] . ' ' . $officeOutRaw);

            $data['over_time']    = $out->greaterThan($officeOut)
                ? $out->diff($officeOut)->format('%H:%I')
                : null;

            $data['is_early_out'] = $out->lessThan($officeOut);
        }

        return $data;
    }
}