<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class FileManagerPersonalizatTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('filemanager');
    }

    public function test_dispatcher_cannot_mutate_file_manager(): void
    {
        $dispatcher = $this->createUserWithRoles('dispecer');

        $this->actingAs($dispatcher)
            ->post('/file-manager-personalizat-director/creaza', [
                'cale' => '',
                'numeDirector' => 'Restricted',
            ])
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->post('/file-manager-personalizat-fisiere/adauga', [
                'cale' => '',
                'fisiere' => [UploadedFile::fake()->create('blocked.txt', 1)],
            ])
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->post('/file-manager-personalizat-resursa/modifica-cale-nume', [
                'cale' => '',
                'numeVechi' => 'old',
                'numeNou' => 'new',
            ])
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->post('/file-manager-personalizat-fisier/copy', [
                'source' => 'Reports/report.txt',
            ])
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->post('/file-manager-personalizat-fisier/move', [
                'source' => 'Reports/report.txt',
            ])
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->post('/file-manager-personalizat-director/copy', [
                'source' => 'Reports',
            ])
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->post('/file-manager-personalizat-director/move', [
                'source' => 'Reports',
            ])
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->delete('/file-manager-personalizat-director/sterge/Reports')
            ->assertForbidden();

        $this->actingAs($dispatcher)
            ->delete('/file-manager-personalizat-fisier/sterge/Reports/report.txt')
            ->assertForbidden();
    }

    public function test_admin_can_manage_file_manager_resources(): void
    {
        $admin = $this->createUserWithRoles('admin');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat')
            ->post('/file-manager-personalizat-director/creaza', [
                'cale' => '',
                'numeDirector' => 'Reports',
            ])
            ->assertRedirect();

        Storage::disk('filemanager')->assertDirectoryExists('Reports');

        $upload = UploadedFile::fake()->create('summary.txt', 1);

        $this->actingAs($admin)
            ->from('/file-manager-personalizat/Reports')
            ->post('/file-manager-personalizat-fisiere/adauga', [
                'cale' => 'Reports',
                'fisiere' => [$upload],
            ])
            ->assertRedirect();

        Storage::disk('filemanager')->assertExists('Reports/summary.txt');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat/Reports')
            ->post('/file-manager-personalizat-resursa/modifica-cale-nume', [
                'cale' => 'Reports',
                'extensieFisier' => 'txt',
                'numeVechi' => 'summary',
                'numeNou' => 'report',
            ])
            ->assertRedirect();

        Storage::disk('filemanager')->assertExists('Reports/report.txt');
        Storage::disk('filemanager')->assertMissing('Reports/summary.txt');

        Storage::disk('filemanager')->makeDirectory('Archive');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat/Reports')
            ->post('/file-manager-personalizat-fisier/copy', [
                'source' => 'Reports/report.txt',
                'destination' => 'Archive',
            ])
            ->assertRedirect();

        Storage::disk('filemanager')->assertExists('Archive/report.txt');

        Storage::disk('filemanager')->makeDirectory('Archive2');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat/Reports')
            ->post('/file-manager-personalizat-fisier/move', [
                'source' => 'Reports/report.txt',
                'destination' => 'Archive2',
            ])
            ->assertRedirect();

        Storage::disk('filemanager')->assertMissing('Reports/report.txt');
        Storage::disk('filemanager')->assertExists('Archive2/report.txt');

        Storage::disk('filemanager')->put('Archive2/nested.txt', 'content');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat')
            ->post('/file-manager-personalizat-director/copy', [
                'source' => 'Archive2',
                'destination' => 'Archive',
            ])
            ->assertRedirect();

        Storage::disk('filemanager')->assertExists('Archive/Archive2/report.txt');
        Storage::disk('filemanager')->assertExists('Archive/Archive2/nested.txt');

        Storage::disk('filemanager')->makeDirectory('Archive3');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat')
            ->post('/file-manager-personalizat-director/move', [
                'source' => 'Archive2',
                'destination' => 'Archive3',
            ])
            ->assertRedirect();

        Storage::disk('filemanager')->assertDirectoryMissing('Archive2');
        Storage::disk('filemanager')->assertExists('Archive3/Archive2/report.txt');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat')
            ->delete('/file-manager-personalizat-fisier/sterge/Archive/Archive2/report.txt')
            ->assertRedirect();

        Storage::disk('filemanager')->assertMissing('Archive/Archive2/report.txt');

        $this->actingAs($admin)
            ->from('/file-manager-personalizat')
            ->delete('/file-manager-personalizat-director/sterge/Archive3/Archive2')
            ->assertRedirect();

        Storage::disk('filemanager')->assertDirectoryMissing('Archive3/Archive2');
    }

}
