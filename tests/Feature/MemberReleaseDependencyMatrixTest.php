<?php

namespace Tests\Feature;

use App\Http\Middleware\EnsureInstalled;
use App\Models\MemberLesson;
use App\Models\MemberLessonProgress;
use App\Models\MemberModule;
use App\Models\MemberSection;
use App\Models\Product;
use App\Models\User;
use Tests\TestCase;

class MemberReleaseDependencyMatrixTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(EnsureInstalled::class);
    }

    public function test_module_progress_thresholds_use_each_required_modules_lesson_count(): void
    {
        [$product, $section, $student] = $this->course();
        $required = $this->module($product, $section, 'Base com quatro aulas', 1, 4);
        $quarter = $this->module($product, $section, 'Libera com 25%', 2, 1);
        $half = $this->module($product, $section, 'Libera com 50%', 3, 1);
        $quarter->releaseDependencies()->create([
            'required_member_module_id' => $required->id,
            'minimum_progress_percent' => 25,
        ]);
        $half->releaseDependencies()->create([
            'required_member_module_id' => $required->id,
            'minimum_progress_percent' => 50,
        ]);

        $this->assertModuleLocked($product, $student, 1, true);
        $this->assertModuleLocked($product, $student, 2, true);

        $this->complete($product, $student, $required->lessons[0]);

        $this->assertModuleLocked($product, $student, 1, false);
        $this->assertModuleLocked($product, $student, 2, true);

        $this->complete($product, $student, $required->lessons[1]);

        $this->assertModuleLocked($product, $student, 2, false);

        $otherStudent = User::factory()->create(['role' => User::ROLE_ALUNO, 'tenant_id' => 1]);
        $product->users()->attach($otherStudent->id);
        $this->assertModuleLocked($product, $otherStudent, 1, true);
    }

    public function test_all_selected_module_dependencies_must_reach_their_own_thresholds(): void
    {
        [$product, $section, $student] = $this->course();
        $first = $this->module($product, $section, 'Primeiro', 1, 4);
        $second = $this->module($product, $section, 'Segundo', 2, 2);
        $target = $this->module($product, $section, 'Destino', 3, 1);
        $target->releaseDependencies()->createMany([
            ['required_member_module_id' => $first->id, 'minimum_progress_percent' => 50],
            ['required_member_module_id' => $second->id, 'minimum_progress_percent' => 100],
        ]);

        $this->complete($product, $student, $first->lessons[0]);
        $this->complete($product, $student, $first->lessons[1]);
        $this->complete($product, $student, $second->lessons[0]);

        $this->assertModuleLocked($product, $student, 2, true);

        $this->complete($product, $student, $second->lessons[1]);

        $this->assertModuleLocked($product, $student, 2, false);
    }

    public function test_schedule_and_progress_conditions_are_both_required(): void
    {
        [$product, $section, $student] = $this->course(now()->subDays(8));
        $required = $this->module($product, $section, 'Base', 1, 2);
        $target = $this->module($product, $section, 'Após sete dias e 50%', 2, 1, 7);
        $target->releaseDependencies()->create([
            'required_member_module_id' => $required->id,
            'minimum_progress_percent' => 50,
        ]);

        $this->assertModuleLocked($product, $student, 1, true, 'Atinja 50%');

        $this->complete($product, $student, $required->lessons[0]);

        $this->assertModuleLocked($product, $student, 1, false);

        $newStudent = User::factory()->create(['role' => User::ROLE_ALUNO, 'tenant_id' => 1]);
        $product->users()->attach($newStudent->id);
        $this->complete($product, $newStudent, $required->lessons[0]);
        $this->assertModuleLocked($product, $newStudent, 1, true, 'Disponível em 7 dias');
    }

    public function test_a_dependency_on_an_empty_module_never_counts_as_completed(): void
    {
        [$product, $section, $student] = $this->course();
        $empty = $this->module($product, $section, 'Módulo vazio', 1, 0);
        $target = $this->module($product, $section, 'Destino', 2, 1);
        $target->releaseDependencies()->create([
            'required_member_module_id' => $empty->id,
            'minimum_progress_percent' => 1,
        ]);

        $this->assertModuleLocked($product, $student, 1, true, 'Atinja 1%');
    }

    public function test_fractional_progress_is_compared_using_the_completed_whole_percent(): void
    {
        [$product, $section, $student] = $this->course();
        $required = $this->module($product, $section, 'Três aulas', 1, 3);
        $at34 = $this->module($product, $section, 'Exige 34%', 2, 1);
        $at67 = $this->module($product, $section, 'Exige 67%', 3, 1);
        $at34->releaseDependencies()->create([
            'required_member_module_id' => $required->id,
            'minimum_progress_percent' => 34,
        ]);
        $at67->releaseDependencies()->create([
            'required_member_module_id' => $required->id,
            'minimum_progress_percent' => 67,
        ]);

        $this->complete($product, $student, $required->lessons[0]);
        $this->assertModuleLocked($product, $student, 1, true);

        $this->complete($product, $student, $required->lessons[1]);
        $this->assertModuleLocked($product, $student, 1, false);
        $this->assertModuleLocked($product, $student, 2, true);

        $this->complete($product, $student, $required->lessons[2]);
        $this->assertModuleLocked($product, $student, 2, false);
    }

    public function test_release_date_and_progress_conditions_are_both_required(): void
    {
        [$product, $section, $student] = $this->course();
        $required = $this->module($product, $section, 'Base', 1, 2);
        $target = $this->module($product, $section, 'Data e progresso', 2, 1);
        $target->update(['release_at_date' => now()->addDay()->format('Y-m-d')]);
        $target->releaseDependencies()->create([
            'required_member_module_id' => $required->id,
            'minimum_progress_percent' => 50,
        ]);
        $this->complete($product, $student, $required->lessons[0]);

        $this->assertModuleLocked($product, $student, 1, true, 'Disponível em');

        $target->update(['release_at_date' => now()->subDay()->format('Y-m-d')]);

        $this->assertModuleLocked($product, $student, 1, false);
    }

    public function test_lesson_schedule_and_previous_lesson_completion_are_both_required(): void
    {
        [$product, $section, $student] = $this->course();
        $module = $this->module($product, $section, 'Aulas', 1, 2);
        [$first, $second] = $module->lessons->all();
        $second->update(['release_at_date' => now()->addDay()->format('Y-m-d')]);
        $second->releaseDependencies()->create(['required_member_lesson_id' => $first->id]);
        $this->complete($product, $student, $first);

        $this->actingAs($student)->get('/m/'.$product->checkout_slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('sections.0.modules.0.lessons.1.is_locked', true)
                ->where('sections.0.modules.0.lessons.1.lock_message', fn ($message) => str_contains((string) $message, 'Disponível em')));

        $second->update(['release_at_date' => now()->subDay()->format('Y-m-d')]);

        $this->actingAs($student)->get('/m/'.$product->checkout_slug)
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('sections.0.modules.0.lessons.1.is_locked', false));
    }

    public function test_reordering_modules_and_sections_removes_dependencies_that_are_no_longer_previous(): void
    {
        [$product, $firstSection, $owner] = $this->course(owner: true);
        $secondSection = MemberSection::create([
            'product_id' => $product->id,
            'title' => 'Segunda seção',
            'position' => 2,
            'section_type' => 'courses',
        ]);
        $first = $this->module($product, $firstSection, 'Primeiro', 1, 1);
        $second = $this->module($product, $firstSection, 'Segundo', 2, 1);
        $third = $this->module($product, $secondSection, 'Terceiro', 1, 1);
        $second->releaseDependencies()->create([
            'required_member_module_id' => $first->id,
            'minimum_progress_percent' => 100,
        ]);
        $third->releaseDependencies()->create([
            'required_member_module_id' => $second->id,
            'minimum_progress_percent' => 100,
        ]);

        $this->actingAs($owner)->postJson(route('member-builder.reorder', $product), [
            'scope' => 'modules',
            'section_id' => $firstSection->id,
            'ordered_ids' => [$second->id, $first->id],
        ])->assertOk();

        $this->assertDatabaseMissing('member_module_release_dependencies', [
            'member_module_id' => $second->id,
            'required_member_module_id' => $first->id,
        ]);
        $this->assertDatabaseHas('member_module_release_dependencies', [
            'member_module_id' => $third->id,
            'required_member_module_id' => $second->id,
        ]);

        $this->actingAs($owner)->postJson(route('member-builder.reorder', $product), [
            'scope' => 'sections',
            'ordered_ids' => [$secondSection->id, $firstSection->id],
        ])->assertOk();

        $this->assertDatabaseMissing('member_module_release_dependencies', [
            'member_module_id' => $third->id,
            'required_member_module_id' => $second->id,
        ]);
    }

    public function test_reordering_lessons_removes_dependencies_that_are_no_longer_previous(): void
    {
        [$product, $section, $owner] = $this->course(owner: true);
        $module = $this->module($product, $section, 'Módulo', 1, 2);
        [$first, $second] = $module->lessons->all();
        $second->releaseDependencies()->create(['required_member_lesson_id' => $first->id]);

        $this->actingAs($owner)->postJson(route('member-builder.reorder', $product), [
            'scope' => 'lessons',
            'module_id' => $module->id,
            'ordered_ids' => [$second->id, $first->id],
        ])->assertOk();

        $this->assertDatabaseMissing('member_lesson_release_dependencies', [
            'member_lesson_id' => $second->id,
            'required_member_lesson_id' => $first->id,
        ]);
    }

    /** @return array{Product, MemberSection, User} */
    private function course($accessStartedAt = null, bool $owner = false): array
    {
        $user = User::factory()->create([
            'role' => $owner ? User::ROLE_INFOPRODUTOR : User::ROLE_ALUNO,
            'tenant_id' => 1,
        ]);
        $product = $this->createTestProduct([
            'type' => Product::TYPE_AREA_MEMBROS,
            'checkout_slug' => 'matrix-'.substr(uniqid('', true), -8),
        ]);
        $section = MemberSection::create([
            'product_id' => $product->id,
            'title' => 'Trilha',
            'position' => 1,
            'section_type' => 'courses',
        ]);
        if (! $owner) {
            $timestamp = $accessStartedAt ?? now();
            $product->users()->attach($user->id, ['created_at' => $timestamp, 'updated_at' => $timestamp]);
        }

        return [$product, $section, $user];
    }

    private function module(
        Product $product,
        MemberSection $section,
        string $title,
        int $position,
        int $lessonCount,
        ?int $releaseAfterDays = null,
    ): MemberModule {
        $module = MemberModule::create([
            'member_section_id' => $section->id,
            'product_id' => $product->id,
            'title' => $title,
            'position' => $position,
            'release_after_days' => $releaseAfterDays,
        ]);
        for ($position = 1; $position <= $lessonCount; $position++) {
            MemberLesson::create([
                'member_module_id' => $module->id,
                'product_id' => $product->id,
                'title' => $title.' - Aula '.$position,
                'position' => $position,
                'type' => MemberLesson::TYPE_TEXT,
                'content_text' => '<p>Conteúdo</p>',
            ]);
        }

        return $module->load('lessons');
    }

    private function complete(Product $product, User $student, MemberLesson $lesson): void
    {
        MemberLessonProgress::updateOrCreate(
            ['user_id' => $student->id, 'member_lesson_id' => $lesson->id],
            ['product_id' => $product->id, 'completed_at' => now(), 'progress_percent' => 100],
        );
    }

    private function assertModuleLocked(
        Product $product,
        User $student,
        int $moduleIndex,
        bool $locked,
        ?string $messageContains = null,
    ): void {
        $assertion = fn ($page) => $page->where("sections.0.modules.{$moduleIndex}.is_locked", $locked);
        if ($messageContains !== null) {
            $assertion = fn ($page) => $page
                ->where("sections.0.modules.{$moduleIndex}.is_locked", $locked)
                ->where("sections.0.modules.{$moduleIndex}.lock_message", fn ($message) => str_contains((string) $message, $messageContains));
        }

        $this->actingAs($student)
            ->get('/m/'.$product->checkout_slug)
            ->assertOk()
            ->assertInertia($assertion);
    }
}
