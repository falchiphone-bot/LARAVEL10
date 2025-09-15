<?php

namespace App\Services;

use Carbon\Carbon;

class HolidayService
{
    /**
     * Retorna true se a data for feriado nacional (ou móvel) no Brasil.
     */
    public function isHoliday(Carbon $date): bool
    {
        $y = (int) $date->year;
        $holidays = $this->listHolidaysForYear($y);
        return isset($holidays[$date->format('Y-m-d')]);
    }

    /**
     * Retorna o dia útil anterior à data informada (exclui a própria data),
     * pulando fins de semana e feriados.
     */
    public function previousBusinessDay(Carbon $date): Carbon
    {
        $d = $date->copy()->subDay();
        while ($d->isWeekend() || $this->isHoliday($d) || $this->isHalfDay($d)) {
            $d->subDay();
        }
        return $d;
    }

    /**
     * Versão com motivo: retorna [date=>Carbon, reason=>string]
     */
    public function previousBusinessDayInfo(Carbon $date): array
    {
        $d = $date->copy()->subDay();
        $reason = '';
        while (true) {
            if ($d->isWeekend()) {
                $reason = $d->isSaturday() ? 'Weekend (Saturday)' : 'Weekend (Sunday)';
            } elseif ($this->isHoliday($d)) {
                $label = $this->getHolidayLabel($d);
                $reason = $label !== '' ? ('Holiday: ' . $label) : 'Holiday';
            } elseif ($this->isHalfDay($d)) {
                $label = $this->getHalfDayLabel($d);
                $reason = $label !== '' ? ('Early Close: ' . $label) : 'Early Close';
            } else {
                break;
            }
            $d->subDay();
        }
        return ['date' => $d, 'reason' => $reason];
    }

    /** Retorna o rótulo do feriado na data (ou vazio). */
    public function getHolidayLabel(Carbon $date): string
    {
        $y = (int)$date->year;
        $map = $this->listHolidaysForYear($y);
        $key = $date->format('Y-m-d');
        return $map[$key] ?? '';
    }

    /** Retorna o rótulo da meia-sessão na data (ou vazio). */
    public function getHalfDayLabel(Carbon $date): string
    {
        $y = (int)$date->year;
        $map = $this->listNYSEHalfDaysForYear($y);
        $key = $date->format('Y-m-d');
        return $map[$key] ?? '';
    }

    /**
     * Lista feriados de mercado dos EUA (NYSE) no formato ['Y-m-d' => 'Nome'].
     * Inclui observância (se cair no fim de semana, move para sexta/segunda).
     */
    public function listHolidaysForYear(int $year): array
    {
    $holidays = [];

        // Utilidades
        $nthWeekday = function(int $y, int $month, int $weekday, int $n): Carbon {
            // weekday: 0=Dom ... 6=Sáb (Carbon)
            $d = Carbon::create($y, $month, 1);
            while ($d->dayOfWeek !== $weekday) { $d->addDay(); }
            // já estamos no 1º weekday, avançar n-1 semanas
            $d->addWeeks($n - 1);
            return $d;
        };
        $lastWeekday = function(int $y, int $month, int $weekday): Carbon {
            $d = Carbon::create($y, $month, 1)->endOfMonth();
            while ($d->dayOfWeek !== $weekday) { $d->subDay(); }
            return $d;
        };
        $observed = function(Carbon $d): Carbon {
            $obs = $d->copy();
            if ($obs->isSaturday()) { $obs->subDay(); }
            elseif ($obs->isSunday()) { $obs->addDay(); }
            return $obs;
        };

        // Fixos com observância: New Year (Jan 1), Juneteenth (Jun 19), Independence Day (Jul 4), Christmas (Dec 25)
        $newYear = $observed(Carbon::create($year, 1, 1));
        $juneteenth = $observed(Carbon::create($year, 6, 19));
        $independence = $observed(Carbon::create($year, 7, 4));
        $christmas = $observed(Carbon::create($year, 12, 25));
        $holidays[$newYear->format('Y-m-d')] = "New Year's Day (Observed)";
        $holidays[$juneteenth->format('Y-m-d')] = 'Juneteenth National Independence Day (Observed)';
        $holidays[$independence->format('Y-m-d')] = 'Independence Day (Observed)';
        $holidays[$christmas->format('Y-m-d')] = 'Christmas Day (Observed)';

        // MLK Day: 3º Monday de Janeiro
        $mlk = $nthWeekday($year, 1, Carbon::MONDAY, 3);
        $holidays[$mlk->format('Y-m-d')] = 'Martin Luther King Jr. Day';
        // Presidents' Day (Washington's Birthday): 3º Monday de Fevereiro
        $presidents = $nthWeekday($year, 2, Carbon::MONDAY, 3);
        $holidays[$presidents->format('Y-m-d')] = "Presidents' Day";
        // Good Friday: 2 dias antes da Páscoa (mercado fecha)
        $goodFriday = $this->getEasterDate($year)->subDays(2);
        $holidays[$goodFriday->format('Y-m-d')] = 'Good Friday';
        // Memorial Day: última Monday de Maio
        $memorial = $lastWeekday($year, 5, Carbon::MONDAY);
        $holidays[$memorial->format('Y-m-d')] = 'Memorial Day';
        // Labor Day: 1º Monday de Setembro
        $labor = $nthWeekday($year, 9, Carbon::MONDAY, 1);
        $holidays[$labor->format('Y-m-d')] = 'Labor Day';
        // Thanksgiving Day: 4º Thursday de Novembro
        $thanksgiving = $nthWeekday($year, 11, Carbon::THURSDAY, 4);
        $holidays[$thanksgiving->format('Y-m-d')] = 'Thanksgiving Day';

        // Fechamentos extraordinários (NYSED): 9/11 (2001-09-11 a 2001-09-14),
        // Funeral Ronald Reagan (2004-06-11), Funeral Gerald Ford (2007-01-02),
        // Hurricane Sandy (2012-10-29, 2012-10-30), Funeral George H.W. Bush (2018-12-05)
        foreach ($this->nyseExtraClosures($year) as $d => $label) {
            $holidays[$d] = $label;
        }

        return $holidays;
    }

    /**
     * Retorna true se a data for meia sessão (early close). Trataremos como não negociado para o cálculo.
     */
    public function isHalfDay(Carbon $date): bool
    {
        $y = (int)$date->year;
        $half = $this->listNYSEHalfDaysForYear($y);
        $key = $date->format('Y-m-d');
        // não considerar meia sessão se for feriado completo
        return isset($half[$key]) && !$this->isHoliday($date);
    }

    /**
     * Datas de meia sessão típicas na NYSE.
     */
    public function listNYSEHalfDaysForYear(int $year): array
    {
        $res = [];
        // Utilidades
        $nthWeekday = function(int $y, int $month, int $weekday, int $n): Carbon {
            $d = Carbon::create($y, $month, 1);
            while ($d->dayOfWeek !== $weekday) { $d->addDay(); }
            $d->addWeeks($n - 1);
            return $d;
        };
        $observed = function(Carbon $d): Carbon {
            $obs = $d->copy();
            if ($obs->isSaturday()) { $obs->subDay(); }
            elseif ($obs->isSunday()) { $obs->addDay(); }
            return $obs;
        };

        // Thanksgiving (4º Thursday de Novembro); half-day na sexta seguinte (Black Friday)
        $thanksgiving = $nthWeekday($year, 11, Carbon::THURSDAY, 4);
        $blackFriday = $thanksgiving->copy()->addDay();
        if ($blackFriday->isWeekday()) {
            $res[$blackFriday->format('Y-m-d')] = 'Early Close (Black Friday)';
        }

        // Christmas Eve (Dec 24) costuma ser meia sessão quando dia útil
        $xmasEve = Carbon::create($year, 12, 24);
        $xmasObs = $observed(Carbon::create($year, 12, 25))->format('Y-m-d');
        if ($xmasEve->isWeekday() && $xmasEve->format('Y-m-d') !== $xmasObs) {
            $res[$xmasEve->format('Y-m-d')] = 'Early Close (Christmas Eve)';
        }

        // Véspera do Independence Day (Jul 3) frequentemente é meia sessão
        $jul3 = Carbon::create($year, 7, 3);
        $jul4Obs = $observed(Carbon::create($year, 7, 4))->format('Y-m-d');
        if ($jul3->isWeekday() && $jul3->format('Y-m-d') !== $jul4Obs) {
            $res[$jul3->format('Y-m-d')] = 'Early Close (Day before Independence Day)';
        }

        return $res;
    }

    /**
     * Fechamentos extraordinários conhecidos da NYSE por ano.
     */
    private function nyseExtraClosures(int $year): array
    {
        $extra = [];
        if ($year === 2001) {
            // 9/11 closures (inclusive até 14/09)
            foreach (['2001-09-11','2001-09-12','2001-09-13','2001-09-14'] as $d) { $extra[$d] = 'NYSE Closed (9/11)'; }
        }
        if ($year === 2004) { $extra['2004-06-11'] = 'NYSE Closed (Reagan Funeral)'; }
        if ($year === 2007) { $extra['2007-01-02'] = 'NYSE Closed (Ford Funeral)'; }
        if ($year === 2012) { $extra['2012-10-29'] = 'NYSE Closed (Hurricane Sandy)'; $extra['2012-10-30'] = 'NYSE Closed (Hurricane Sandy)'; }
        if ($year === 2018) { $extra['2018-12-05'] = 'NYSE Closed (Bush Sr. Funeral)'; }
        return $extra;
    }

    /**
     * Calcula a data da Páscoa (algoritmo de Butcher) para o ano dado.
     */
    private function getEasterDate(int $year): Carbon
    {
        $a = $year % 19;
        $b = intdiv($year, 100);
        $c = $year % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $month = intdiv($h + $l - 7 * $m + 114, 31); // 3=Março, 4=Abril
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;
        return Carbon::create($year, $month, $day, 0, 0, 0);
    }

    /**
     * Retorna o status da sessão da NYSE no momento informado.
     * status: 'pre' | 'open' | 'after' | 'closed'
     * Campos: tz, now, date, labels, horários de referência e next_change_at
     */
    public function marketSessionInfoNY(?Carbon $now = null): array
    {
        $tz = 'America/New_York';
        $n = ($now ? $now->copy() : Carbon::now())->setTimezone($tz);
        $dateKey = $n->format('Y-m-d');
        $weekday = $n->isWeekday();

        // Configuração padrão NYSE
        $preStart = Carbon::createFromTime(4, 0, 0, $tz)->setDate($n->year, $n->month, $n->day);   // 04:00
        $regOpen  = Carbon::createFromTime(9, 30, 0, $tz)->setDate($n->year, $n->month, $n->day);  // 09:30
        $regClose = Carbon::createFromTime(16, 0, 0, $tz)->setDate($n->year, $n->month, $n->day);  // 16:00
        $aftEnd   = Carbon::createFromTime(20, 0, 0, $tz)->setDate($n->year, $n->month, $n->day);  // 20:00

        $closedReason = '';
        $isHoliday = $this->isHoliday($n);
        $isHalf = $this->isHalfDay($n); // early close
        if ($isHalf) {
            // Em meia sessão, fechamento às 13:00 e pós-mercado não é considerado
            $regClose = Carbon::createFromTime(13, 0, 0, $tz)->setDate($n->year, $n->month, $n->day);
            $aftEnd = $regClose->copy();
        }

        // Situações de fechamento completo
        if (!$weekday) {
            $closedReason = $n->isSaturday() ? 'Fim de semana (sábado)' : 'Fim de semana (domingo)';
        } elseif ($isHoliday) {
            $lbl = $this->getHolidayLabel($n);
            $closedReason = $lbl !== '' ? ('Feriado: ' . $lbl) : 'Feriado';
        }

        $status = 'closed';
        $label = 'Fechado';
        $nextChange = null;

        if ($closedReason !== '') {
            // Fechado o dia todo
            $status = 'closed';
            $label = 'Fechado';
            $nextChange = null;
        } else {
            if ($n->lt($preStart)) {
                $status = 'closed';
                $label = 'Fechado';
                $nextChange = $preStart;
            } elseif ($n->lt($regOpen)) {
                $status = 'pre';
                $label = 'Pré-mercado';
                $nextChange = $regOpen;
            } elseif ($n->lt($regClose)) {
                $status = 'open';
                $label = $isHalf ? 'Em mercado (meia sessão)' : 'Em mercado';
                $nextChange = $regClose;
            } elseif ($n->lt($aftEnd)) {
                $status = 'after';
                $label = $isHalf ? 'Fechado (meia sessão)' : 'After-hours';
                $nextChange = $aftEnd;
            } else {
                $status = 'closed';
                $label = 'Fechado';
                $nextChange = null;
            }
        }

        return [
            'market' => 'NYSE',
            'timezone' => $tz,
            'now' => $n->format('Y-m-d H:i:s'),
            'date' => $dateKey,
            'status' => $status,
            'label' => $label,
            'reason' => $closedReason ?: null,
            'pre_start' => $preStart->format('H:i'),
            'regular_open' => $regOpen->format('H:i'),
            'regular_close' => $regClose->format('H:i'),
            'after_end' => $aftEnd->format('H:i'),
            'half_day' => $isHalf,
            'next_change_at' => $nextChange ? $nextChange->format('Y-m-d H:i:s') : null,
        ];
    }
}
