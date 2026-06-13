<?php

namespace App\Filament\Widgets;

use App\Enums\PaymentStatusEnum;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\DatePicker;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class PaymentsChart extends ChartWidget implements HasActions
{
    use InteractsWithActions;

    protected ?string $heading = 'Pagos aprobados';

    protected int|string|array $columnSpan = '50%';

    protected ?string $maxHeight = '400px';

    protected string $view = 'filament.widgets.payments-chart';

    public ?string $filter = '30';

    public ?string $fromDate = null;

    public ?string $toDate = null;

    protected function getFilters(): ?array
    {
        return [
            '7' => 'Últimos 7 días',
            '15' => 'Últimos 15 días',
            '30' => 'Últimos 30 días',
            '90' => 'Últimos 3 meses',
            'custom' => 'Personalizado',
        ];
    }

    public function updatedFilter($value): void
    {
        if ($value === 'custom') {
            $this->mountAction('customRange');

            return;
        }

        $this->cachedData = null;
    }

    public function customRangeAction(): Action
    {
        return Action::make('customRange')
            ->modalHeading('Rango personalizado')
            ->modalSubmitActionLabel('Aplicar')
            ->fillForm(fn (): array => [
                'fromDate' => $this->fromDate ?? now()->subDays(29)->toDateString(),
                'toDate' => $this->toDate ?? now()->toDateString(),
            ])
            ->schema([
                DatePicker::make('fromDate')
                    ->label('Desde')
                    ->native(false)
                    ->required()
                    ->maxDate(now()),
                DatePicker::make('toDate')
                    ->label('Hasta')
                    ->native(false)
                    ->required()
                    ->maxDate(now())
                    ->afterOrEqual('fromDate'),
            ])
            ->action(function (array $data): void {
                $this->fromDate = $data['fromDate'];
                $this->toDate = $data['toDate'];
                $this->cachedData = null;
                $this->updateChartData();
            });
    }

    protected function getData(): array
    {
        [$from, $to] = $this->resolveRange();

        $totalDays = $from->diffInDays($to) + 1;
        $groupByMonth = $totalDays > 60;

        $query = Order::query()
            ->where('payment_status', PaymentStatusEnum::PAID)
            ->whereBetween('updated_at', [$from, $to]);

        if ($groupByMonth) {
            $rows = $query
                ->selectRaw("DATE_FORMAT(updated_at, '%Y-%m') as bucket, COUNT(*) as count")
                ->groupBy('bucket')
                ->pluck(DB::raw('count'), 'bucket');
        } else {
            $rows = $query
                ->selectRaw('DATE(updated_at) as bucket, COUNT(*) as count')
                ->groupBy('bucket')
                ->pluck(DB::raw('count'), 'bucket');
        }

        $labels = [];
        $counts = [];

        if ($groupByMonth) {
            for ($date = $from->copy()->startOfMonth(); $date <= $to; $date->addMonth()) {
                $key = $date->format('Y-m');
                $labels[] = $date->translatedFormat('M Y');
                $counts[] = (int) ($rows[$key] ?? 0);
            }
        } else {
            for ($date = $from->copy(); $date <= $to; $date->addDay()) {
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('d/m');
                $counts[] = (int) ($rows[$key] ?? 0);
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Pagos aprobados',
                    'data' => $counts,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.15)',
                    'fill' => true,
                    'tension' => 0.3,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => ['precision' => 0],
                ],
            ],
            'plugins' => [
                'legend' => ['display' => false],
            ],
            'maintainAspectRatio' => false,
        ];
    }

    private function resolveRange(): array
    {
        if ($this->filter === 'custom' && $this->fromDate && $this->toDate) {
            return [
                Carbon::parse($this->fromDate)->startOfDay(),
                Carbon::parse($this->toDate)->endOfDay(),
            ];
        }

        $days = (int) ($this->filter ?? 30);
        $days = $days > 0 ? $days : 30;

        return [
            now()->subDays($days - 1)->startOfDay(),
            now()->endOfDay(),
        ];
    }
}
