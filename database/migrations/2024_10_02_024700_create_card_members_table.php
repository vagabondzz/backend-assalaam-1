<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('card_members', function (Blueprint $table) {
            $table->id();
            $table->string('nama', 50)->nullable();
            $table->string('no_member', 20)->unique()->nullable();
            $table->string('tempat_lahir', 50)->nullable();
            $table->string('tanggal_lahir', 50)->nullable();
            $table->string('no_identitas')->nullable();
            $table->enum('jenis_kelamin', ['Laki-laki', 'Perempuan'])->nullable();
            $table->string('alamat', 250)->nullable();
            $table->string('rt_rw', 20)->nullable();
            $table->string('kelurahan', 20)->nullable();
            $table->string('kecamatan', 50)->nullable();
            $table->string('kota', 20)->nullable();
            $table->string('kode_pos', 20)->nullable();
            $table->string('no_hp', 50)->nullable();
            $table->string('status', 20)->nullable();
            $table->string('jumlah_tanggungan', 50)->nullable();
            $table->decimal('pendapatan', 50)->nullable();
            $table->string('npwp', 50)->nullable();
            $table->string('kewarganegaraan', 50)->nullable();
            $table->string('agama', 20)->nullable();
            $table->boolean('validation')->default(false)->nullable();
            $table->string('member_profile', 255)->nullable();
            $table->string('active_start')->nullable();
            $table->string('active_end')->nullable();
            $table->boolean('is_active')->default(false)->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('card_members');
    }
};
