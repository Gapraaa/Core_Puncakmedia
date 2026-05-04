<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('villa_facilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('villa_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20);
            $table->string('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['villa_id', 'type', 'sort_order']);
        });

        if (Schema::hasColumns('villas', ['facilities', 'additional_facilities'])) {
            $villas = DB::table('villas')
                ->select(['id', 'facilities', 'additional_facilities'])
                ->get();

            foreach ($villas as $villa) {
                $primaryFacilities = preg_split('/\r\n|\r|\n/', (string) ($villa->facilities ?? '')) ?: [];
                $additionalFacilities = preg_split('/\r\n|\r|\n/', (string) ($villa->additional_facilities ?? '')) ?: [];

                foreach (array_values(array_filter(array_map('trim', $primaryFacilities))) as $index => $facility) {
                    DB::table('villa_facilities')->insert([
                        'villa_id' => $villa->id,
                        'type' => 'primary',
                        'name' => $facility,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                foreach (array_values(array_filter(array_map('trim', $additionalFacilities))) as $index => $facility) {
                    DB::table('villa_facilities')->insert([
                        'villa_id' => $villa->id,
                        'type' => 'additional',
                        'name' => $facility,
                        'sort_order' => $index,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            Schema::table('villas', function (Blueprint $table) {
                $table->dropColumn(['facilities', 'additional_facilities']);
            });
        }
    }

    public function down(): void
    {
        Schema::table('villas', function (Blueprint $table) {
            if (! Schema::hasColumn('villas', 'facilities')) {
                $table->text('facilities')->nullable()->after('description');
            }

            if (! Schema::hasColumn('villas', 'additional_facilities')) {
                $table->text('additional_facilities')->nullable()->after('facilities');
            }
        });

        if (Schema::hasTable('villa_facilities')) {
            $groupedFacilities = DB::table('villa_facilities')
                ->orderBy('villa_id')
                ->orderBy('type')
                ->orderBy('sort_order')
                ->get()
                ->groupBy('villa_id');

            foreach ($groupedFacilities as $villaId => $facilities) {
                $primary = $facilities->where('type', 'primary')->pluck('name')->implode(PHP_EOL);
                $additional = $facilities->where('type', 'additional')->pluck('name')->implode(PHP_EOL);

                DB::table('villas')->where('id', $villaId)->update([
                    'facilities' => $primary ?: null,
                    'additional_facilities' => $additional ?: null,
                ]);
            }
        }

        Schema::dropIfExists('villa_facilities');
    }
};
