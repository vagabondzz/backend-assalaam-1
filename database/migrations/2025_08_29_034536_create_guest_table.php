<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest', function (Blueprint $table) {
            $table->string('MEMBER_ID')->primary();
            $table->boolean('IS_TRADER')->nullable();
            $table->string('MEMBER_CARD_NO')->nullable();
            $table->string('MEMBER_NAME')->nullable();
            $table->boolean('MEMBER_IS_WNI')->nullable();
            $table->string('MEMBER_PLACE_OF_BIRTH')->nullable();
            $table->date('MEMBER_DATE_OF_BIRTH')->nullable();
            $table->string('MEMBER_KTP_NO')->nullable();
            $table->string('MEMBER_SEX', 10)->nullable();
            $table->text('MEMBER_ADDRESS')->nullable();
            $table->string('MEMBER_KElURAHAN')->nullable();
            $table->string('MEMBER_KECAMATAN')->nullable();
            $table->string('MEMBER_KOTA')->nullable();
            $table->string('MEMBER_RT')->nullable();
            $table->string('MEMBER_RW')->nullable();
            $table->string('MEMBER_POST_CODE')->nullable();
            $table->integer('MEMBER_JML_TANGGUNGAN')->nullable();
            $table->decimal('MEMBER_PENDAPATAN', 15, 2)->nullable();
            $table->string('MEMBER_TELP')->nullable();
            $table->boolean('MEMBER_IS_MARRIED')->nullable();
            $table->boolean('MEMBER_IS_MAIN')->nullable();
            $table->dateTime('MEMBER_REGISTERED_DATE')->nullable();
            $table->string('MEMBER_GROMEMBER_ID')->nullable();
            $table->string('MEMBER_GROMEMBER_UNT_ID')->nullable();
            $table->boolean('MEMBER_IS_VALID')->nullable();
            $table->boolean('MEMBER_IS_ACTIVE')->nullable();
            $table->dateTime('DATE_CREATE')->nullable();
            $table->dateTime('DATE_MODIFY')->nullable();
            $table->decimal('MEMBER_TOP', 15, 2)->nullable();
            $table->decimal('MEMBER_PLAFON', 15, 2)->nullable();
            $table->integer('MEMBER_LEAD_TIME')->nullable();
            $table->integer('MEMBER_POIN')->nullable();
            $table->string('REF$TIPE_PEMBAYARAN_ID')->nullable();
            $table->string('MEMBER_ACTIVASI_ID')->nullable();
            $table->string('MEMBER_KELUARGA_ID')->nullable();
            $table->string('REF$DISC_MEMBER_ID')->nullable();
            $table->string('REF$GRUP_MEMBER_ID')->nullable();
            $table->string('REF$TIPE_MEMBER_ID')->nullable();
            $table->string('REF$AGAMA_ID')->nullable();
            $table->string('MEMBER_REK_PIUTANG_ID')->nullable();
            $table->integer('MEMBER_KUPON')->nullable();
            $table->string('MEMBER_FAX')->nullable();
            $table->date('MEMBER_ACTIVE_FROM')->nullable();
            $table->date('MEMBER_ACTIVE_TO')->nullable();
            $table->string('MEMBER_NPWP')->nullable();
            $table->boolean('MEMBER_IS_DEFAULT')->nullable();
            $table->boolean('MEMBER_IS_PKP')->nullable();
            $table->string('USER_CREATE')->nullable();
            $table->string('USER_MODIFY')->nullable();
            $table->boolean('ISMAGIC')->nullable();
            $table->boolean('ISWEB')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest');
    }
};
