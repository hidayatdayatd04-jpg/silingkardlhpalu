<?php

namespace Database\Seeders;

use App\Models\ProfilDinas;
use Illuminate\Database\Seeder;

class ProfilDinasEnglishSeeder extends Seeder
{
    public function run(): void
    {
        $profil = ProfilDinas::current();

        $profil->update([
            'visi_en' => '<p>Realizing a Palu City that is Clean, Green, Sustainable, and Resilient to Environmental Disasters.</p>',
            'misi_en' => '<ol><li>Improving the quality of integrated cleanliness and waste management services.</li><li>Optimizing Green Open Space (RTH) maintenance and protective tree risk mitigation.</li><li>Increasing public awareness and active participation in environmental preservation.</li><li>Realizing good corporate governance based on information technology.</li></ol>',
            'tugas_fungsi_en' => '<h3>Main Duties</h3><p>The Palu City Environmental Agency has the main duty of implementing regional government affairs in the environmental sector, including environmental protection and management, waste management, and green open space management.</p><h3>Main Functions</h3><ul><li>Environmental protection and management</li><li>Waste and B3 waste management</li><li>Green open space management</li><li>Environmental monitoring and enforcement</li><li>Community empowerment in environmental management</li></ul>',
        ]);
    }
}
