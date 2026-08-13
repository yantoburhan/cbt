<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;
use App\Models\Question;
use App\Models\Answer;
use Faker\Factory as Faker;

class QuestionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('id_ID');

        $subjects = ['Matematika', 'Bahasa Indonesia', 'Kimia'];

        foreach ($subjects as $subjectName) {
            $subject = Subject::create([
                'name' => $subjectName,
                'description' => 'Soal ujian untuk mata pelajaran ' . $subjectName,
                'is_active' => true,
            ]);

            // Membuat 150 soal untuk setiap mata pelajaran (Total 450 soal)
            for ($i = 1; $i <= 150; $i++) {
                $qData = $this->generateQuestionData($subjectName, $faker);

                $question = Question::create([
                    'subject_id' => $subject->id,
                    'content' => $qData['question'],
                    'score' => 1,
                    'is_active' => true,
                ]);

                $prefixes = ['A', 'B', 'C', 'D', 'E'];
                
                // Menyimpan jawaban yang bertaut pada pertanyaan
                foreach ($qData['options'] as $index => $optionText) {
                    $isCorrect = ((string) $optionText === (string) $qData['correct']);
                    
                    Answer::create([
                        'question_id' => $question->id,
                        'text' => $prefixes[$index] . '. ' . $optionText,
                        'is_correct' => $isCorrect,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    /**
     * Helper untuk membuat data dummy berdasarkan mata pelajaran
     */
    private function generateQuestionData($subject, $faker)
    {
        if ($subject === 'Matematika') {
            return $this->mathGenerator($faker);
        } elseif ($subject === 'Bahasa Indonesia') {
            return $this->indonesianGenerator($faker);
        } elseif ($subject === 'Kimia') {
            return $this->chemistryGenerator($faker);
        }
        return ['question' => 'Unknown', 'options' => [], 'correct' => ''];
    }

    private function mathGenerator($faker)
    {
        $type = rand(1, 4); // 4 Topik Berbeda
        
        if ($type === 1) { 
            // 1. Aritmatika Dasar
            $a = rand(10, 100);
            $b = rand(1, 50);
            $ops = ['+', '-', '*', '/'];
            $op = $ops[array_rand($ops)];
            if ($op === '/') { $a = $b * rand(2, 20); }
            $question = "Berapakah hasil perhitungan dari $a $op $b ?";
            $ans = ($op === '+') ? ($a + $b) : (($op === '-') ? ($a - $b) : (($op === '*') ? ($a * $b) : ($a / $b)));
        } 
        elseif ($type === 2) { 
            // 2. Geometri (Luas/Keliling)
            $shapes = ['persegi', 'persegi panjang', 'segitiga siku-siku'];
            $shape = $shapes[array_rand($shapes)];
            if ($shape === 'persegi') {
                $s = rand(5, 30);
                $question = "Sebuah persegi memiliki panjang sisi $s cm. Berapakah luas bangun tersebut?";
                $ans = $s * $s;
            } elseif ($shape === 'persegi panjang') {
                $p = rand(10, 30);
                $l = rand(5, 15);
                $question = "Sebuah persegi panjang memiliki panjang $p cm dan lebar $l cm. Luas bangun tersebut adalah...";
                $ans = $p * $l;
            } else {
                $a = rand(4, 20);
                $t = rand(5, 20);
                $question = "Sebuah segitiga siku-siku memiliki panjang alas $a cm dan tinggi $t cm. Berapakah luas segitiga tersebut?";
                $ans = 0.5 * $a * $t;
            }
            $question .= " (dalam cm²)";
        }
        elseif ($type === 3) { 
            // 3. Aljabar Sederhana
            $a = rand(2, 10);
            $x = rand(1, 20);
            $b = rand(1, 30);
            $c = ($a * $x) + $b;
            $question = "Tentukan nilai x yang memenuhi persamaan linear berikut: {$a}x + $b = $c";
            $ans = $x;
        }
        else { 
            // 4. Statistika Dasar (Rata-rata)
            $n1 = rand(10, 50); 
            $n2 = rand(10, 50); 
            $n3 = rand(10, 50); 
            $n4 = rand(10, 50);
            $ans = ($n1 + $n2 + $n3 + $n4) / 4;
            $question = "Nilai ujian 4 orang siswa adalah $n1, $n2, $n3, dan $n4. Berapakah nilai rata-rata dari keempat siswa tersebut?";
        }

        $options = [$ans];
        while (count($options) < 5) {
            $fake = (is_float($ans)) ? $ans + (rand(-10, 10) * 0.5) : $ans + rand(-15, 15);
            if (!in_array($fake, $options)) {
                $options[] = $fake;
            }
        }
        
        $correct = $ans;
        shuffle($options);
        return ['question' => $question, 'options' => $options, 'correct' => $correct];
    }

    private function indonesianGenerator($faker)
    {
        $type = rand(1, 4); // 4 Topik Berbeda
        
        if ($type === 1) {
            // 1. Kalimat Utama & Gagasan Pokok
            $pos = ['di awal paragraf (Deduktif)', 'di akhir paragraf (Induktif)', 'di awal dan akhir paragraf (Campuran)', 'menyebar di seluruh paragraf (Deskriptif)', 'tidak memiliki kalimat utama'];
            $question = "Berdasarkan letak kalimat utamanya, jenis paragraf di bawah ini adalah...\n\n\"" . $faker->paragraph(3) . "\"";
            $options = $pos;
            $correct = $pos[array_rand($pos)]; // Di dummy ini kunci jawabannya kita acak secara cerdas
        } 
        elseif ($type === 2) {
            // 2. Sinonim / Antonim
            $isSinonim = rand(0, 1);
            $word = $faker->word();
            $kata = $isSinonim ? "sinonim (persamaan makna)" : "antonim (lawan makna)";
            $question = "Dalam Kamus Besar Bahasa Indonesia (KBBI), $kata yang paling tepat untuk kata '$word' adalah...";
            $options = [$faker->word()];
            while (count($options) < 5) { 
                $fake = $faker->word();
                if (!in_array($fake, $options)) $options[] = $fake; 
            }
            $correct = $options[0];
        }
        elseif ($type === 3) {
            // 3. Majas / Gaya Bahasa
            $majas = ['Personifikasi', 'Metafora', 'Hiperbola', 'Litotes', 'Ironi'];
            $target = $majas[array_rand($majas)];
            $question = "Kalimat di bawah ini yang menggunakan majas $target adalah...";
            $options = ["Kalimat yang memuat majas " . strtolower($target) . " (" . $faker->words(3, true) . ")."];
            while(count($options) < 5) { 
                $fake = ucfirst($faker->sentence(4));
                if (!in_array($fake, $options)) $options[] = $fake; 
            }
            $correct = $options[0];
        }
        else {
            // 4. Ejaan & Kata Baku
            $question = "Berdasarkan Pedoman Umum Ejaan Bahasa Indonesia (PUEBI), kalimat manakah yang penulisan huruf kapitalnya paling tepat?";
            $options = [
                "Presiden Republik Indonesia memberikan sambutan.", // Benar
                "Gunung semeru kembali mengalami Erupsi pagi ini.", // Salah
                "Ayah membaca koran kompas di Teras rumah.", // Salah
                "sungai Kapuas adalah sungai terpanjang di indonesia.", // Salah
                "Hari ini kita akan belajar bahasa Inggris bersama." // Salah
            ];
            $correct = $options[0];
        }
        
        shuffle($options);
        return ['question' => $question, 'options' => $options, 'correct' => $correct];
    }

    private function chemistryGenerator($faker)
    {
        $type = rand(1, 4); // 4 Topik Berbeda
        $options = [];
        
        if ($type === 1) { 
            // 1. Struktur Atom & Konfigurasi
            $elements = ['Oksigen', 'Karbon', 'Nitrogen', 'Natrium', 'Klorin'];
            $el = $elements[array_rand($elements)];
            $question = "Berapakah jumlah proton pada atom $el dalam keadaan netral?";
            $correct = (string) rand(1, 20);
            $options = [$correct];
            while(count($options) < 5) {
                $fake = (string) rand(1, 30);
                if (!in_array($fake, $options)) $options[] = $fake;
            }
        }
        elseif ($type === 2) { 
            // 2. Tatanama Senyawa Kimia
            $compounds = [
                'H2O' => 'Air (Dihidrogen Monoksida)',
                'CO2' => 'Karbon Dioksida',
                'NaCl' => 'Natrium Klorida',
                'H2SO4' => 'Asam Sulfat',
                'CH3COOH' => 'Asam Asetat'
            ];
            $rumus = array_rand($compounds);
            $nama = $compounds[$rumus];
            $question = "Nama senyawa atau zat kimia yang tepat untuk rumus molekul $rumus adalah...";
            $correct = $nama;
            $options = [$nama, 'Karbon Monoksida', 'Natrium Hidroksida', 'Asam Klorida', 'Besi(III) Oksida'];
        }
        elseif ($type === 3) { 
            // 3. Ikatan Kimia Dasar
            $question = "Ikatan yang terbentuk akibat adanya serah terima elektron antara unsur logam dan non-logam sehingga membentuk ion positif dan negatif disebut...";
            $options = ['Ikatan Ion', 'Ikatan Kovalen', 'Ikatan Logam', 'Ikatan Hidrogen', 'Ikatan Kovalen Koordinasi'];
            $correct = 'Ikatan Ion';
        }
        else { 
            // 4. Asam Basa
            $question = "Berdasarkan teori asam-basa Arrhenius, suatu zat cair dikategorikan sebagai asam apabila zat tersebut...";
            $options = [
                'Menghasilkan ion H+ ketika dilarutkan dalam air', // Benar
                'Menghasilkan ion OH- ketika dilarutkan dalam air',
                'Dapat menerima pasangan elektron bebas dari senyawa lain',
                'Memberikan pasangan elektron bebas',
                'Memiliki rentang pH di antara angka 8 sampai 14'
            ];
            $correct = $options[0];
        }

        shuffle($options);
        return ['question' => $question, 'options' => $options, 'correct' => $correct];
    }
}
