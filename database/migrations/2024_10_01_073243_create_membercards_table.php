<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMembercardsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('membercards', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50);
            $table->string('no_member', 20)->unique();
            $table->string('tempat_lahir', 50);
            $table->date('tanggal_lahir');
            $table->string('no_identitas')->unique();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan']);
            $table->string('alamat', 250);
            $table->string('rt_rw', 20);
            $table->string('kelurahan', 20);
            $table->string('kota', 20);
            $table->string('kode_pos', 20);
            $table->string('no_hp', 20);
            $table->string('status', 20);
            $table->string('jumlah_tanggungan', 50);
            $table->decimal('pendapatan', 50);
            $table->string('npwp', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('membercards');
    }
}
