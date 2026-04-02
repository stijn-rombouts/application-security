<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Test;
use App\Models\Question;
use App\Models\InterestField;

class TestSeederOne extends Seeder
{
    /**
     * Seed the test database table with one test and three questions.
     */
    public function run(): void
    {
        // Create the "DEMO TEST"
        $test = Test::create([
            'test_name' => 'DEMO TEST',
            'active' => true,
        ]);

        $landTuinbouw = InterestField::create(['name' => 'Land- en tuinbouw', 'description' => 'Beschrijving van Land- en tuinbouw']);
        $huishoudelijkWerk = InterestField::create(['name' => 'Huishoudelijk werk', 'description' => 'Beschrijving van Huishoudelijk werk']);
        $klantgerichtWerk = InterestField::create(['name' => 'Klantgericht werk', 'description' => 'Beschrijving van Klantgericht werk']);
        $logistiekEnTransport = InterestField::create(['name' => 'Logistiek en transport', 'description' => 'Beschrijving van Logistiek en transport']);
        $onderhoud = InterestField::create(['name' => 'Onderhoud', 'description' => 'Beschrijving van Onderhoud']);
        $technischWerk = InterestField::create(['name' => 'Technisch werk', 'description' => 'Beschrijving van Technisch werk']);
        $voedselbereidendWerk = InterestField::create(['name' => 'Voedselbereidend werk', 'description' => 'Beschrijving van Voedselbereidend werk']);
        $zorgEnWelzijn = InterestField::create(['name' => 'Zorg en welzijn', 'description' => 'Beschrijving van Zorg en welzijn']);
        $semiIndustrieelWerk = InterestField::create(['name' => 'Semi-industrieel werk', 'description' => 'Beschrijving van Semi-industrieel werk']);
        $administratiefWerk = InterestField::create(['name' => 'Administratief werk', 'description' => 'Beschrijving van Administratief werk']);
        $dierenverzorging = InterestField::create(['name' => 'Dierenverzorging', 'description' => 'Beschrijving van Dierenverzorging']);

        // Define the questions and their corresponding images and interest fields
        $questions = [
            ['image' => 'work-in-the-garden-2432111_1920.jpg', 'name' => 'Onkruid wieden', 'interest_field_id' => $landTuinbouw->interest_field_id],
            ['image' => 'Huishoudelijk5.jpg', 'name' => 'Ramen wassen', 'interest_field_id' => $huishoudelijkWerk->interest_field_id],
            ['image' => '3027724237_737bba0687_o.jpg', 'name' => 'Vragen beantwoorden', 'interest_field_id' => $administratiefWerk->interest_field_id],
            ['image' => 'Land- en tuinbouw5.jpg', 'name' => 'Gras maaien', 'interest_field_id' => $landTuinbouw->interest_field_id],
            ['image' => 'shipping (1).jpg', 'name' => 'Goederen verplaatsen', 'interest_field_id' => $logistiekEnTransport->interest_field_id],
            ['image' => 'afval.jpg', 'name' => 'Recycleren', 'interest_field_id' => $onderhoud->interest_field_id],
            ['image' => 'Schilderwerk-Multi-Concurrent.jpg', 'name' => 'Schilderwerken', 'interest_field_id' => $technischWerk->interest_field_id],
            ['image' => 'bakers-858394_1920.jpg', 'name' => 'Brood bakken', 'interest_field_id' => $voedselbereidendWerk->interest_field_id],
            ['image' => 'kindergarten-90505_1280.jpg', 'name' => 'Staartjes maken', 'interest_field_id' => $zorgEnWelzijn->interest_field_id],
            ['image' => 'Producten verpakken.jpg', 'name' => 'In zakjes steken', 'interest_field_id' => $semiIndustrieelWerk->interest_field_id],
            ['image' => 'paper-3249922_1920.jpg', 'name' => 'Formulieren invullen', 'interest_field_id' => $administratiefWerk->interest_field_id],
            ['image' => 'WhatsApp Image 2019-09-13 at 13.30.43.jpeg', 'name' => 'Eieren rapen', 'interest_field_id' => $dierenverzorging->interest_field_id],
            ['image' => 'Huishoudelijk4.jpg', 'name' => 'Was plooien', 'interest_field_id' => $huishoudelijkWerk->interest_field_id],
            ['image' => 'Klantgericht4.jpeg', 'name' => 'Kassa', 'interest_field_id' => $klantgerichtWerk->interest_field_id],
            ['image' => 'P1040085.JPG', 'name' => 'Materiaal wegbrengen', 'interest_field_id' => $logistiekEnTransport->interest_field_id],
            ['image' => 'zwerfvuil-01-large-.jpg', 'name' => 'Afval rapen', 'interest_field_id' => $onderhoud->interest_field_id],
            ['image' => 'solder-1038522_1280.jpg', 'name' => 'Solderen', 'interest_field_id' => $technischWerk->interest_field_id],
            ['image' => 'De-8-meest-gemaakte-fouten-bij-het-afwassen-2.jpg', 'name' => 'Afwassen', 'interest_field_id' => $huishoudelijkWerk->interest_field_id],
            ['image' => 'Zorg en Welzijn4\'.jpg', 'name' => 'Luiers verversen', 'interest_field_id' => $zorgEnWelzijn->interest_field_id],
            ['image' => 'WhatsApp Image 2019-09-13 at 09.40.23.jpeg', 'name' => 'In elkaar steken', 'interest_field_id' => $semiIndustrieelWerk->interest_field_id],
            ['image' => 'Administratief4\'.jpg', 'name' => 'Lamineren', 'interest_field_id' => $administratiefWerk->interest_field_id],
            ['image' => 'Dierenverzorging4.jpg', 'name' => 'Dieren uitlaten', 'interest_field_id' => $dierenverzorging->interest_field_id],
            ['image' => 'Huishoudelijk3.jpg', 'name' => 'Wassen', 'interest_field_id' => $huishoudelijkWerk->interest_field_id],
            ['image' => '4379374300_bbfeeffda5_z.jpg', 'name' => 'Gidsen', 'interest_field_id' => $klantgerichtWerk->interest_field_id],
            ['image' => 'postbode-brievenbus-postnl.jpg', 'name' => 'Post rondbrengen', 'interest_field_id' => $logistiekEnTransport->interest_field_id],
            ['image' => 'Picking Apples.jpg', 'name' => 'Fruit plukken', 'interest_field_id' => $landTuinbouw->interest_field_id],
            ['image' => '6215697457_faa8a03f41_b.jpg', 'name' => 'Auto\'s wassen', 'interest_field_id' => $onderhoud->interest_field_id],
            ['image' => 'Technisch werk3.jpeg', 'name' => 'Meubels schuren', 'interest_field_id' => $technischWerk->interest_field_id],
            ['image' => 'tafel-dekken-kerst-diner-foto-pexels-e1541595952613.jpg', 'name' => 'Tafels dekken', 'interest_field_id' => $klantgerichtWerk->interest_field_id],
            ['image' => 'Zorg en welzijn3.jpeg', 'name' => 'Wandelen met de rolwagen', 'interest_field_id' => $zorgEnWelzijn->interest_field_id],
        ];

        // Create questions and associate them with interest fields
        $questionNumber = 1;
        foreach ($questions as $questionData) {
            Question::create([
                'test_id' => $test->test_id,
                'media_link' => $questionData['image'],
                'image_description' => $questionData['name'],
                'question' => $questionData['name'],
                'interest_field_id' => $questionData['interest_field_id'],
                'question_number' => $questionNumber++,
            ]);
        }

        $clientUser = DB::table('users')->where('username', 'client')->first();

        DB::table('user_test')->insert([
            'user_id' => $clientUser->user_id,
            'test_id' => $test->test_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
