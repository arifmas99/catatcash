<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Wallet;
use App\Services\ReportAggregatorService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(private readonly ReportAggregatorService $report)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'period' => ['nullable', Rule::in(['daily', 'weekly', 'monthly', 'custom'])],
            'date' => ['nullable', 'date'],
            'week_start' => ['nullable', 'date'],
            'month' => ['nullable', 'date_format:Y-m'],
            'start_date' => ['nullable', 'date', 'required_if:period,custom'],
            'end_date' => ['nullable', 'date', 'required_if:period,custom', 'after_or_equal:start_date'],
            'wallet_id' => ['nullable', 'string'],
            'category_filter' => ['nullable', 'string'],
        ], [
            'date.date' => 'Tanggal tidak valid.',
            'week_start.date' => 'Tanggal minggu tidak valid.',
            'month.date_format' => 'Format bulan tidak valid.',
            'start_date.required_if' => 'Tanggal mulai wajib diisi untuk periode Custom.',
            'end_date.required_if' => 'Tanggal akhir wajib diisi untuk periode Custom.',
            'end_date.after_or_equal' => 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir.',
        ]);

        $filters = [
            'period' => $validated['period'] ?? 'monthly',
            'date' => $validated['date'] ?? null,
            'week_start' => $validated['week_start'] ?? null,
            'month' => $validated['month'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'wallet_id' => $request->query('wallet_id', 'all') ?: 'all',
            'category_filter' => $request->query('category_filter', 'all') ?: 'all',
        ];

        return view('pages.reports.index', [
            'filters' => $filters,
            'summary' => $this->report->summary($filters),
            'chart' => $this->report->chartSeries($filters),
            'transactions' => $this->report->transactions($filters),
            'periodLabel' => $this->periodLabel($filters),
            'wallets' => Wallet::query()->orderByDesc('is_primary')->orderBy('name')->get(),
            'categories' => Category::query()->orderBy('id')->get(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    private function periodLabel(array $filters): string
    {
        $months = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $range = $this->report->resolveRange($filters);

        if (($filters['period'] ?? 'monthly') === 'monthly') {
            return $months[(int) $range['start']->month].' '.$range['start']->year;
        }

        $start = $range['start']->format('d/m/Y');
        $end = $range['end']->format('d/m/Y');

        return $start === $end ? $start : $start.' – '.$end;
    }
}