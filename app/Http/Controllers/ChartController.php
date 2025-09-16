<?php

namespace App\Http\Controllers;

use App\Models\CardMember;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;

class ChartController extends Controller
{
    public function getYearlyChart()
    {
        // $members = CardMember::paginate(10);
        // dd('oke');
        $monthData = CardMember::selectRaw('Month(created_at) as month, COUNT(*) as jumlah')
            ->whereYear('created_at', Carbon::now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        $months = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember',
        ];
        $memberCounts = array_fill(0, 12, 0);
        foreach ($monthData as $data) {
            $memberCounts[$data->month - 1] = $data->jumlah;
        }
        $chart = (new LarapexChart)->areaChart()
            // ->setTitle('Jumlah Anggota yang Masuk Per Bulan')
            ->addData('Jumlah Anggota', $memberCounts)
            ->setXAxis($months)
            ->setColors(['#FFC107', '#303F9F'])
            ->setGrid()
            ->setMarkers(['#FF5722', '#E040FB'], 7, 10);

        return view('auth.dashboard', compact('chart'));
    }
}
