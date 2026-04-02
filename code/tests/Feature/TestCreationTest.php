<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use App\Livewire\Roles\Superadmin\TestCreation;
use App\Models\Test;
use App\Models\Question;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

// stub routes used by the component
beforeEach(function () {
    Route::get('/question/sound/{filename}', fn ($f) => $f)->name('question.sound');
    Route::get('/question/image/{filename}', fn ($f) => $f)->name('question.image');

    // if your UI needs interest fields
    \App\Models\InterestField::factory()->count(2)->create();
});

test('mount creates a blank question when no session data', function () {
    Livewire::test(TestCreation::class)
        ->assertSet('selectedQuestion', 0)
        ->assertSet('questions.0.circleFill', 'red');
});

test('mount loads edit session data when present', function () {
    $questions = [[
        'question_number' => 1,
        'title' => 'Loaded Q',
        'description' => 'Desc',
        'interest' => 1,
        'circleFill' => 'green',
        'media_link' => null,
        'sound_link' => null,
    ]];

    session([
        'edit_test_id' => 123,
        'edit_test_name' => 'Edited Test',
        'edit_questions' => $questions,
    ]);

    Livewire::test(TestCreation::class)
        ->assertSet('test_id', 123)
        ->assertSet('test_name', 'Edited Test')
        ->assertSet('questions.0.title', 'Loaded Q');
});

test('uploadSound stores file and sets sound_link', function () {
    Storage::fake('public');
    $audio = UploadedFile::fake()->create('rec.webm', 100, 'video/webm');

    Livewire::test(TestCreation::class)
        // triggers updated() which calls uploadSound()
        ->set('questions.0.uploaded_sound', $audio)
        ->assertSet('questions.0.uploaded_sound', null)
        ->assertSet('questions.0.has_audio', true)
        ->tap(function ($component) {
            $filename = $component->get('questions')[0]['sound_link'] ?? null;
            expect($filename)->toBeString();
            Storage::disk('public')->assertExists($filename);
        });
});

test('clearSound deletes file and resets state', function () {
    Storage::fake('public');

    $filename = 'q0_'.uniqid().'.webm';
    Storage::disk('public')->put($filename, 'dummy');

    Livewire::test(TestCreation::class)
        ->set('questions.0.sound_link', $filename)
        ->set('questions.0.has_audio', true)
        ->call('clearSound', 0)
        ->assertSet('questions.0.sound_link', null)
        ->assertSet('questions.0.has_audio', false);

    Storage::disk('public')->assertMissing($filename);
});

test('uploadImage stores file and sets media_link', function () {
    Storage::fake('public');
    // avoid GD, fake a plain file with image mime
    $img = UploadedFile::fake()->create('pic.jpg', 100, 'image/jpeg');

    Livewire::test(TestCreation::class)
        // triggers updated() which calls uploadImage()
        ->set('questions.0.uploaded_image', $img)
        ->assertSet('questions.0.uploaded_image', null)
        ->tap(function ($component) {
            $filename = $component->get('questions')[0]['media_link'] ?? null;
            expect($filename)->toBeString();
            Storage::disk('public')->assertExists($filename);
        });
});

test('updated hook changes circleFill based on inputs', function () {
    Livewire::test(TestCreation::class)
        ->set('questions.0.title', 'Hello')
        ->assertSet('questions.0.circleFill', 'yellow')
        ->set('questions.0.interest', 1)
        ->assertSet('questions.0.circleFill', 'green')
        ->set('questions.0.title', '')
        ->set('questions.0.interest', -1)
        ->assertSet('questions.0.circleFill', 'red');
});

test('reorderQuestions changes order and renumbers', function () {
    $lw = Livewire::test(TestCreation::class)
        ->call('createQuestion') // indexes 0 and 1
        ->set('questions.0.title', 'A')
        ->set('questions.1.title', 'B');

    $lw->call('reorderQuestions', 0, 1)
       ->assertSet('questions.0.title', 'B')
       ->assertSet('questions.1.title', 'A')
       ->tap(function ($c) {
           $q = $c->get('questions');
           expect($q[0]['question_number'])->toBe(1);
           expect($q[1]['question_number'])->toBe(2);
       });
});

test('uploadTest creates test and questions in DB when all green', function () {
    Livewire::test(TestCreation::class)
        ->set('test_name', 'My Test')
        ->set('questions.0.title', 'Q1')
        ->set('questions.0.interest', 1)
        ->set('questions.0.circleFill', 'green')
        ->call('uploadTest');

    $test = Test::first();
    expect($test)->not->toBeNull()
        ->and($test->test_name)->toBe('My Test');

    $q = Question::where('test_id', $test->test_id)->first();
    expect($q)->not->toBeNull()
        ->and($q->question)->toBe('Q1')
        ->and($q->interest_field_id)->toBe(1);
});

test('uploadTest updates existing test and replaces questions', function () {
    $test = Test::create(['test_name' => 'Old']);
    Question::create([
        'test_id' => $test->test_id,
        'interest_field_id' => 1,
        'question_number' => 1,
        'question' => 'Old Q',
        'image_description' => '',
        'media_link' => null,
        'sound_link' => null,
    ]);

    session([
        'edit_test_id' => $test->test_id,
        'edit_test_name' => 'Old',
        'edit_questions' => [[
            'question_number' => 1,
            'title' => 'Edited Q',
            'description' => 'Edited',
            'interest' => 2,
            'circleFill' => 'green',
            'media_link' => null,
            'sound_link' => null,
        ]],
    ]);

    Livewire::test(TestCreation::class)
        ->set('test_name', 'Updated Name')
        ->call('uploadTest');

    $test->refresh();
    expect($test->test_name)->toBe('Updated Name');
    expect(Question::where('test_id', $test->test_id)->count())->toBe(1);
    $q = Question::where('test_id', $test->test_id)->first();
    expect($q->question)->toBe('Edited Q');
    expect($q->interest_field_id)->toBe(2);
});

test('setting uploaded_sound triggers uploadSound via updated hook', function () {
    Storage::fake('public');
    $audio = UploadedFile::fake()->create('rec.webm', 100, 'video/webm');

    Livewire::test(TestCreation::class)
        ->set('questions.0.uploaded_sound', $audio)
        ->assertSet('questions.0.uploaded_sound', null)
        ->tap(function ($c) {
            $filename = $c->get('questions')[0]['sound_link'] ?? null;
            expect($filename)->toBeString();
            Storage::disk('public')->assertExists($filename);
        });
});

test('setting uploaded_image triggers uploadImage via updated hook', function () {
    Storage::fake('public');
    $img = UploadedFile::fake()->create('img.jpg', 100, 'image/jpeg');

    Livewire::test(TestCreation::class)
        ->set('questions.0.uploaded_image', $img)
        ->assertSet('questions.0.uploaded_image', null)
        ->tap(function ($c) {
            $filename = $c->get('questions')[0]['media_link'] ?? null;
            expect($filename)->toBeString();
            Storage::disk('public')->assertExists($filename);
        });
});

test('sound url is passed to the view when sound_link set', function () {
    Livewire::test(\App\Livewire\Roles\Superadmin\TestCreation::class)
        ->set('questions.0.sound_link', 'q0_abc.webm')
        ->assertViewHas('soundUrl', fn ($url) =>
            is_string($url) && str_contains($url, '/question/sound/q0_abc.webm')
        );
});

