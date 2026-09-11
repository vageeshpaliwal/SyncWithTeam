<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->text('allowed_ips')->nullable()->after('currency');
            $table->decimal('geofence_latitude', 10, 8)->nullable()->after('allowed_ips');
            $table->decimal('geofence_longitude', 11, 8)->nullable()->after('geofence_latitude');
            $table->integer('geofence_radius_meters')->default(200)->after('geofence_longitude');
            $table->boolean('enforce_ip_restriction')->default(false)->after('geofence_radius_meters');
            $table->boolean('enforce_geofence')->default(false)->after('enforce_ip_restriction');
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->string('clock_in_ip')->nullable()->after('clock_out_at');
            $table->decimal('clock_in_latitude', 10, 8)->nullable()->after('clock_in_ip');
            $table->decimal('clock_in_longitude', 11, 8)->nullable()->after('clock_in_latitude');
            $table->string('clock_in_address')->nullable()->after('clock_in_longitude');
            $table->string('clock_out_ip')->nullable()->after('clock_in_address');
            $table->decimal('clock_out_latitude', 10, 8)->nullable()->after('clock_out_ip');
            $table->decimal('clock_out_longitude', 11, 8)->nullable()->after('clock_out_latitude');
            $table->string('clock_out_address')->nullable()->after('clock_out_longitude');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'allowed_ips',
                'geofence_latitude',
                'geofence_longitude',
                'geofence_radius_meters',
                'enforce_ip_restriction',
                'enforce_geofence',
            ]);
        });

        Schema::table('attendances', function (Blueprint $table) {
            $table->dropColumn([
                'clock_in_ip',
                'clock_in_latitude',
                'clock_in_longitude',
                'clock_in_address',
                'clock_out_ip',
                'clock_out_latitude',
                'clock_out_longitude',
                'clock_out_address',
            ]);
        });
    }
};