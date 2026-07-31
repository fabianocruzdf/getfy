<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureInstalled;
use App\Models\MemberLesson;
use App\Models\MemberModule;
use App\Models\MemberSection;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class MemberModuleReleaseDependencyTest extends TestCase
{
    public function test_module_release_can_require_a_previous_module_to_be_completed(): void
    {
        $this->withoutMiddleware(EnsureInstalled::class);

        $owner = User::factory()->create(['role' => User::ROLE_INFOPRODUTOR, 'tenant_id' => 1]);
        $student = User::factory()->create(['role' => User::ROLE_ALUNO, 'tenant_id' => 1]);
        $product = $this->createTestProduct([
            'type' => Product::TYPE_AREA_MEMBROS,
            'checkout_slug' => 'release-dependency-'.substr(uniqid('', true), -8),
        ]);
        $section = MemberSection::create([
            'product_id' => $product->id,
            'title' => 'Aulas',
            'position' => 1,
            'section_type' => 'courses',
        ]);
        $firstModule = MemberModule::create([
            'member_section_id' => $section->id,
            'product_id' => $product->id,
            'title' => 'Fundamentos',
            'position' => 1,
        ]);
        $firstLesson = MemberLesson::create([
            'member_module_id' => $firstModule->id,
            'product_id' => $product->id,
            'title' => 'Aula inicial',
            'position' => 1,
            'type' => MemberLesson::TYPE_TEXT,
            'content_text' => '<p>Conteúdo</p>',
        ]);
        MemberLesson::create([
            'member_module_id' => $firstModule->id,
            'product_id' => $product->id,
            'title' => 'Aula complementar',
            'position' => 2,
            'type' => MemberLesson::TYPE_TEXT,
            'content_text' => '<p>Conteúdo</p>',
        ]);

        $response = $this->actingAs($owner)->postJson(
            route('member-builder.modules.store', ['produto' => $product, 'section' => $section]),
            [
                'title' => 'Avançado',
                'release_at_date' => now()->subDay()->format('Y-m-d'),
                'release_dependencies' => [[
                    'module_id' => $firstModule->id,
                    'minimum_progress_percent' => 50,
                ]],
            ],
        );

        $response->assertOk()
            ->assertJsonPath('module.release_dependencies.0.module_id', $firstModule->id)
            ->assertJsonPath('module.release_dependencies.0.minimum_progress_percent', 50);
        $secondModule = MemberModule::where('title', 'Avançado')->firstOrFail();
        $this->assertDatabaseHas('member_module_release_dependencies', [
            'member_module_id' => $secondModule->id,
            'required_member_module_id' => $firstModule->id,
            'minimum_progress_percent' => 50,
        ]);

        $product->users()->attach($student->id);
        $locked = $this->actingAs($student)->get('/m/'.$product->checkout_slug);
        $locked->assertOk()->assertInertia(fn ($page) => $page->where('sections.0.modules.1.is_locked', true));

        $this->actingAs($student)->postJson('/m/'.$product->checkout_slug.'/aula/'.$firstLesson->id.'/complete')
            ->assertOk()
            ->assertJsonPath('success', true);

        $unlocked = $this->actingAs($student)->get('/m/'.$product->checkout_slug);
        $unlocked->assertOk()->assertInertia(fn ($page) => $page->where('sections.0.modules.1.is_locked', false));
    }

    public function test_module_cannot_depend_on_itself_or_a_later_module(): void
    {
        $this->withoutMiddleware(EnsureInstalled::class);

        $owner = User::factory()->create(['role' => User::ROLE_INFOPRODUTOR, 'tenant_id' => 1]);
        $product = $this->createTestProduct(['type' => Product::TYPE_AREA_MEMBROS]);
        $section = MemberSection::create([
            'product_id' => $product->id,
            'title' => 'Aulas',
            'position' => 1,
            'section_type' => 'courses',
        ]);
        $firstModule = MemberModule::create(['member_section_id' => $section->id, 'product_id' => $product->id, 'title' => 'Primeiro', 'position' => 1]);
        $secondModule = MemberModule::create(['member_section_id' => $section->id, 'product_id' => $product->id, 'title' => 'Segundo', 'position' => 2]);

        $this->actingAs($owner)->putJson(
            route('member-builder.modules.update', ['produto' => $product, 'module' => $firstModule]),
            ['release_after_days' => 1, 'release_dependencies' => [[
                'module_id' => $secondModule->id,
                'minimum_progress_percent' => 100,
            ]]],
        )->assertUnprocessable()->assertJsonValidationErrors('release_dependencies');
    }
}
