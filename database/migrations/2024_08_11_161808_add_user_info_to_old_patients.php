<?php

use App\Models\Patient;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Fetch all patients
        $patients = Patient::all();

        foreach ($patients as $patient) {
            if (!$patient->info) { // Only add if UserInfo does not exist
                $patient->info()->create([
                    'numberPrefix' => '+963', // Replace with actual logic
                    'country' => 'Syria',     // Replace with actual logic
                ]);
            }
        }
    }

    public function down()
    {
        // Optionally, you can delete the info entries if needed
        $patients = Patient::all();
        foreach ($patients as $patient) {
            if ($patient->info) {
                $patient->info()->delete();
            }
        }
    }
};
