<?php

namespace App\Filament\Widgets;

use App\Enums\EnumContributionGroup;
use App\Models\User;
use Filament\Widgets\DoughnutChartWidget;
use Illuminate\Database\Eloquent\Builder;

class CommunityChart extends DoughnutChartWidget
{
    protected function getData(): array
    {
        return [
            'labels' => [
                trans('Full users'),
                trans('Sustaining members'),
                trans('Former members'),
                trans('Future members'),
            ],
            'datasets' => [
                [
                    'label' => trans('Community'),
                    'data' => [
                        User::query()
                            ->where(User::COL_JOIN_DATE, '<=', now())
                            ->where(
                                fn (Builder $builder) => $builder
                                    ->where(User::COL_EXIT_DATE, '>=', now())
                                    ->orWhereNull(User::COL_EXIT_DATE)
                            )
                            ->where(User::COL_CONTRIBUTION_GROUP, '=', EnumContributionGroup::FULL_MEMBER)
                            ->count(),
                        User::query()
                            ->where(User::COL_JOIN_DATE, '<=', now())
                            ->where(
                                fn (Builder $builder) => $builder
                                    ->where(User::COL_EXIT_DATE, '>=', now())
                                    ->orWhereNull(User::COL_EXIT_DATE)
                            )
                            ->where(User::COL_CONTRIBUTION_GROUP, '=', EnumContributionGroup::SUSTAINING_MEMBER)
                            ->count(),
                        User::query()
                            ->where(User::COL_EXIT_DATE, '<', now())
                            ->count(),
                        User::query()
                            ->where(User::COL_JOIN_DATE, '>', now())
                            ->count(),
                    ],
                    'backgroundColor' => [
                        'rgb(34,139,34)',
                        'rgb(0,128,0)',
                        'rgb(220,220,220)',
                        'rgb(255,255,224)',
                    ],
                    'hoverOffset' => 4
                ],
            ],
        ];
    }
}
