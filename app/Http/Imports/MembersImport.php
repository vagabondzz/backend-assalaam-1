<?php

namespace App\Imports;

use App\Models\CardMember;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MembersImport implements ToModel
{
    /**
     * @param  array  $row
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public $existing_members = [];

    public function model(array $row)
    {
        if ($row[0] == 'MEMBER_CARD_NO' || empty($row[1])) {
            return null;
        }

        $tempat_lahir = $row[3] != '-' ? $row[3] : null;
        // $tanggal_lahir = Carbon::instance(Date::excelToDateTimeObject($row[4]))->format('Y-m-d');
        $tanggal_lahir = ! empty($row[4]) && is_numeric($row[4])
    ? Carbon::instance(Date::excelToDateTimeObject($row[4]))->format('Y-m-d')
    : null;
        $no_identitas = $row[5] != 'NULL' ? $row[5] : null;

        $jenis_kelamin = $row[6];
        if ($jenis_kelamin == 0) {
            $jenis_kelamin = 'perempuan';
        } elseif ($jenis_kelamin == 1) {
            $jenis_kelamin = 'laki-laki';
        } else {
            $jenis_kelamin = null;
        }

        $alamat = $row[8] != '-' ? $row[8] : null;
        $rt_rw = $row[9] != '' || $row[10] != '' ? $row[9].'/'.$row[10] : null;
        $kelurahan = $row[11] != 'NULL' ? $row[11] : null;
        $kecamatan = $row[12] != 'NULL' ? $row[12] : null;
        $kota = $row[13] != '-' ? $row[13] : null;
        $kode_pos = $row[14] != 0 ? $row[14] : null;
        $no_hp = $row[15] != 0 ? $row[15] : null;

        $status = $row[16];
        if ($status == 1) {
            $status = 'menikah';
        } elseif ($status == 0) {
            $status = 'lajang';
        } else {
            $status = null;
        }

        $pendapatan = $row[18] != '' && $row[18] != 'NULL' ? $row[18] : 0;
        $kewarganegaraan = $row[2] == 1 ? 'wni' : 'wna';
        $active_start = Carbon::instance(Date::excelToDateTimeObject($row[23]))->format('Y-m-d');
        $active_end = Carbon::instance(Date::excelToDateTimeObject($row[24]))->format('Y-m-d');
        $is_active = $active_end > Carbon::today() ? true : false;

        $member = CardMember::where('no_member', $row[0])->first();
        if ($member) {
            $this->existing_members[] = [
                'nama' => $row[1],
                'no_member' => $row[0],
                'tempat_lahir' => $tempat_lahir,
                'tanggal_lahir' => $tanggal_lahir,
                'no_identitas' => $no_identitas,
                'jenis_kelamin' => $jenis_kelamin,
                'alamat' => $alamat,
                'rt_rw' => $rt_rw,
                'kelurahan' => $kelurahan,
                'kecamatan' => $kecamatan,
                'kota' => $kota,
                'kode_pos' => $kode_pos,
                'no_hp' => $no_hp,
                'status' => $status,
                'jumlah_tanggungan' => $row[17],
                'pendapatan' => $pendapatan,
                'npwp' => $row[20],
                'kewarganegaraan' => $kewarganegaraan,
                'agama' => $row[7],
                'member_profile' => null,
                'active_start' => $active_start,
                'active_end' => $active_end,
                'is_active' => $is_active,
            ];

            return null;
        }

        return new CardMember([
            'nama' => $row[1],
            'no_member' => $row[0],
            'tempat_lahir' => $tempat_lahir,
            'tanggal_lahir' => $tanggal_lahir,
            'no_identitas' => $no_identitas,
            'jenis_kelamin' => $jenis_kelamin,
            'alamat' => $alamat,
            'rt_rw' => $rt_rw,
            'kelurahan' => $kelurahan,
            'kecamatan' => $kecamatan,
            'kota' => $kota,
            'kode_pos' => $kode_pos,
            'no_hp' => $no_hp,
            'status' => $status,
            'jumlah_tanggungan' => $row[17],
            'pendapatan' => $pendapatan,
            'npwp' => $row[20],
            'kewarganegaraan' => $kewarganegaraan,
            'agama' => $row[7],
            // 'validation' => $row[22],
            'member_profile' => null,
            'active_start' => $active_start,
            'active_end' => $active_end,
            'is_active' => $is_active,
        ]);
    }
}
