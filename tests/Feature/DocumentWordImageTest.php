<?php

namespace Tests\Feature;

use App\Models\DocumentWord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesUsersWithRoles;
use Tests\TestCase;

class DocumentWordImageTest extends TestCase
{
    use RefreshDatabase;
    use CreatesUsersWithRoles;

    public function test_authorized_user_can_upload_image_for_document_word(): void
    {
        Storage::fake('documente_word_images');

        $user = $this->createUserWithRoles('documente-word-operator');

        $response = $this->actingAs($user)->postJson(route('documente-word.images'), [
            'image' => UploadedFile::fake()->image('editor-upload.png', 800, 600),
        ]);

        $response->assertCreated();
        $response->assertJsonStructure([
            'url',
            'path',
            'disk',
            'original_name',
            'mime_type',
            'size',
        ]);

        $path = $response->json('path');
        $this->assertNotEmpty($path);
        $response->assertJsonPath('url', route('documente-word.images.show', ['path' => $path], false));
        Storage::disk('documente_word_images')->assertExists($path);
        $this->get(route('documente-word.images.show', ['path' => $path]))
            ->assertOk();
    }

    public function test_unauthorized_user_cannot_upload_document_word_images(): void
    {
        Storage::fake('documente_word_images');

        $user = $this->createUserWithRoles('mecanic');

        $response = $this->actingAs($user)->postJson(route('documente-word.images'), [
            'image' => UploadedFile::fake()->image('blocked.png'),
        ]);

        $response->assertForbidden();
    }

    public function test_document_word_payload_accepts_image_nodes(): void
    {
        $user = $this->createUserWithRoles('documente-word-operator');

        $payload = [
            'nume' => 'Document cu imagine',
            'continut' => json_encode([
                'type' => 'doc',
                'content' => [
                    [
                        'type' => 'paragraph',
                        'content' => [
                            ['type' => 'text', 'text' => 'Imagine înainte'],
                        ],
                    ],
                    [
                        'type' => 'image',
                        'attrs' => [
                            'src' => 'https://example.com/image.png',
                            'alt' => 'Example image',
                            'title' => 'Example image',
                        ],
                    ],
                ],
            ]),
        ];

        $response = $this->actingAs($user)->post('/documente-word', $payload);

        $response->assertRedirect('/documente-word');

        $this->assertDatabaseHas('documente_word', [
            'nume' => 'Document cu imagine',
        ]);

        $document = DocumentWord::where('nume', 'Document cu imagine')->firstOrFail();
        $this->assertJson($document->continut);

        $decoded = json_decode($document->continut, true);
        $this->assertSame('image', $decoded['content'][1]['type']);
        $this->assertSame('https://example.com/image.png', $decoded['content'][1]['attrs']['src']);
    }
}
