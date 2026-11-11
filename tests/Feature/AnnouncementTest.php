<?php

namespace Tests\Feature;

use App\Models\Announcement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AnnouncementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_the_board_lists_and_filters_announcements(): void
    {
        Announcement::create([
            'title' => 'Bolos da Vila',
            'category' => 'Alimentação',
            'description' => 'Bolos caseiros para encomenda.',
            'offer_info' => 'Entrega grátis no bairro',
            'contact' => '11999998888',
            'management_token' => hash('sha256', 'bolos-private-code'),
        ]);
        Announcement::create([
            'title' => 'Aulas de violão',
            'category' => 'Educação',
            'description' => 'Aulas para iniciantes.',
            'offer_info' => 'Primeira aula experimental',
            'contact' => 'professor@example.com',
            'management_token' => hash('sha256', 'aulas-private-code'),
        ]);

        $this->get('/?q=bolos&category=Alimenta%C3%A7%C3%A3o')
            ->assertOk()
            ->assertSee('Bolos da Vila')
            ->assertSee('Falar com anunciante')
            ->assertDontSee('Excluir este anúncio')
            ->assertDontSee('Aulas de violão');
    }

    public function test_an_announcement_can_be_created_edited_and_deleted(): void
    {
        $data = [
            'title' => 'Horta da vizinha',
            'category' => 'Outros',
            'description' => 'Verduras fresquinhas colhidas na semana.',
            'offer_info' => 'Cestas a partir de R$ 30',
            'contact' => '11912345678',
        ];

        $response = $this->post(route('announcements.store'), $data)
            ->assertRedirect(route('announcements.index'))
            ->assertSessionHas('status');

        $announcement = Announcement::firstOrFail();
        $managementUrl = $response->getSession()->get('management_url');
        $managementCode = basename(parse_url($managementUrl, PHP_URL_PATH));

        $this->assertIsString($managementUrl);
        $this->assertSame(64, strlen($managementCode));
        $this->assertSame('Horta da vizinha', $announcement->title);
        $this->assertSame('https://wa.me/5511912345678', $announcement->contact_url);
        $this->assertSame(hash('sha256', $managementCode), $announcement->management_token);
        $this->get($managementUrl)->assertOk()->assertSee('Editar anúncio');

        $this->put(route('announcements.update', [$announcement, $managementCode]), [
            ...$data,
            'title' => 'Horta da comunidade',
        ])->assertRedirect(route('announcements.index'));

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id, 'title' => 'Horta da comunidade']);

        $this->delete(route('announcements.destroy', [$announcement, $managementCode]))
            ->assertRedirect(route('announcements.index'));

        $this->assertDatabaseMissing('announcements', ['id' => $announcement->id]);
    }

    public function test_an_invalid_management_code_cannot_change_an_announcement(): void
    {
        $announcement = Announcement::create([
            'title' => 'Serviço local',
            'category' => 'Casa e serviços',
            'description' => 'Pequenos reparos.',
            'offer_info' => 'Orçamento sem compromisso',
            'contact' => '11999998888',
            'management_token' => hash('sha256', 'correct-private-code'),
        ]);

        $this->get(route('announcements.edit', [$announcement, 'wrong-code']))->assertNotFound();
        $this->delete(route('announcements.destroy', [$announcement, 'wrong-code']))->assertNotFound();

        $this->assertDatabaseHas('announcements', ['id' => $announcement->id]);
    }

    public function test_an_optional_image_is_saved_and_removed_when_the_announcement_is_deleted(): void
    {
        Storage::fake('public');

        $image = UploadedFile::fake()->createWithContent(
            'offer.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a0y8AAAAASUVORK5CYII=', true),
        );

        $response = $this->post(route('announcements.store'), [
            'title' => 'Produto com foto',
            'category' => 'Artesanato',
            'description' => 'Peça artesanal feita à mão.',
            'offer_info' => 'Pronta entrega',
            'contact' => 'artesao@example.com',
            'image' => $image,
        ])->assertRedirect(route('announcements.index'));

        $announcement = Announcement::firstOrFail();
        $managementCode = basename(parse_url($response->getSession()->get('management_url'), PHP_URL_PATH));
        Storage::disk('public')->assertExists($announcement->image_path);
        $this->get(route('announcements.index'))
            ->assertOk()
            ->assertSee(Storage::disk('public')->url($announcement->image_path));

        $this->delete(route('announcements.destroy', [$announcement, $managementCode]))
            ->assertRedirect(route('announcements.index'));

        Storage::disk('public')->assertMissing($announcement->image_path);
    }

    public function test_announcement_fields_and_category_are_validated(): void
    {
        $this->from(route('announcements.create'))
            ->post(route('announcements.store'), [
                'title' => 'Anúncio inválido',
                'category' => 'Categoria que não existe',
                'description' => 'Descrição',
                'offer_info' => 'Oferta',
            ])
            ->assertRedirect(route('announcements.create'))
            ->assertSessionHasErrors(['category', 'contact']);

        $this->assertDatabaseCount('announcements', 0);
    }
}
