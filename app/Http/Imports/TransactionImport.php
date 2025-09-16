<?php

namespace App\Imports;

use App\Models\CardMember;
use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class TransactionImport implements ToModel
{
    /**
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        if ($row[0] == 'TRANS_NO' || empty($row[1])) {
            return null;
        }

        $trans_date = Carbon::instance(Date::excelToDateTimeObject($row[2]))->format('Y-m-d h:i:s');

        $member = CardMember::where('no_member', $row[1])->first();
        if (! $member) {
            return throw new \Exception("No member: $row[1] tidak ditemukan.");
        }

        $transaction = Transaction::where('trans_no', $row[0])->first();
        if ($transaction) {
            return throw new \Exception("Data dengan No. transaksi: $row[0] sudah ada.");
        }

        return new Transaction([
            'trans_no' => $row[0],
            'member_card_no' => $row[1],
            'trans_date' => $trans_date,
            'trans_total_transaction' => $row[3],
            'trans_poin_pas' => $row[4],
            'card_member_id' => $member->id,
        ]);
    }
}
