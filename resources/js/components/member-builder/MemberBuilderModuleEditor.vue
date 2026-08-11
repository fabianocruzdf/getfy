<script setup>
import { computed } from 'vue';
import Button from '@/components/ui/Button.vue';
import Toggle from '@/components/ui/Toggle.vue';
import { ShoppingBag, ExternalLink } from 'lucide-vue-next';

const props = defineProps({
    section: { type: Object, required: true },
    module: { type: Object, required: true },
    inputClass: { type: String, required: true },
    uploadLimits: { type: Object, required: true },
    tenantProducts: { type: Array, default: () => [] },
    editingTitle: { type: String, default: '' },
    editingShowTitleOnCover: { type: Boolean, default: true },
    editingRelatedProductId: { default: null },
    editingAccessType: { type: String, default: 'paid' },
    editingExternalUrl: { type: String, default: '' },
    editingReleaseMode: { type: String, default: 'none' },
    editingReleaseAfterDays: { type: String, default: '' },
    editingReleaseAtDate: { type: String, default: '' },
    editingAccessDurationDays: { type: String, default: '' },
    editingRequiresPreviousModules: { type: Boolean, default: false },
    editingReleaseDependencies: { type: Array, default: () => [] },
    editingReleaseDependencyOptions: { type: Array, default: () => [] },
    editingThumbnail: { type: String, default: null },
    thumbnailUploading: { type: Boolean, default: false },
    saving: { type: Boolean, default: false },
});

const emit = defineEmits([
    'update:editingTitle',
    'update:editingShowTitleOnCover',
    'update:editingRelatedProductId',
    'update:editingAccessType',
    'update:editingExternalUrl',
    'update:editingReleaseMode',
    'update:editingReleaseAfterDays',
    'update:editingReleaseAtDate',
    'update:editingAccessDurationDays',
    'update:editingRequiresPreviousModules',
    'update:editingReleaseDependencies',
    'save',
    'pick-thumbnail',
    'remove-thumbnail',
    'set-show-title-on-cover',
]);

const availableDependencyOptions = computed(() => props.editingReleaseDependencyOptions.filter(
    (candidate) => !props.editingReleaseDependencies.some((dependency) => Number(dependency.module_id) === Number(candidate.id)),
));

function emitDependencies(dependencies) {
    emit('update:editingReleaseDependencies', dependencies);
}

function addDependency(moduleId) {
    const id = Number(moduleId);
    if (!id || props.editingReleaseDependencies.some((dependency) => Number(dependency.module_id) === id)) return;
    emitDependencies([...props.editingReleaseDependencies, { module_id: id, minimum_progress_percent: 100 }]);
}

function removeDependency(moduleId) {
    emitDependencies(props.editingReleaseDependencies.filter((dependency) => Number(dependency.module_id) !== Number(moduleId)));
}

function updateDependencyProgress(moduleId, value) {
    emitDependencies(props.editingReleaseDependencies.map((dependency) => Number(dependency.module_id) === Number(moduleId)
        ? { ...dependency, minimum_progress_percent: Number(value) }
        : dependency));
}

function dependencyTitle(moduleId) {
    return props.editingReleaseDependencyOptions.find((candidate) => Number(candidate.id) === Number(moduleId))?.title ?? 'Módulo';
}
</script>

<template>
    <div class="min-h-0 flex-1 space-y-3 overflow-y-auto">
        <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">Editar módulo</p>
        <div>
            <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Título</label>
            <input
                :value="editingTitle"
                type="text"
                :class="inputClass"
                class="w-full"
                @input="emit('update:editingTitle', $event.target.value)"
                @keydown.enter="emit('save')"
            />
        </div>

        <template v-if="(section.section_type ?? 'courses') === 'products'">
            <div>
                <label class="mb-1 flex items-center gap-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    <ShoppingBag class="h-3.5 w-3.5" /> Produto relacionado
                </label>
                <select
                    :value="editingRelatedProductId"
                    :class="inputClass"
                    class="w-full"
                    @change="emit('update:editingRelatedProductId', $event.target.value ? Number($event.target.value) : null)"
                >
                    <option :value="null">Selecione o produto</option>
                    <option v-for="p in tenantProducts" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Acesso</label>
                <select
                    :value="editingAccessType"
                    :class="inputClass"
                    class="w-full"
                    @change="emit('update:editingAccessType', $event.target.value)"
                >
                    <option value="paid">Pago</option>
                    <option value="free">Liberado</option>
                </select>
            </div>
        </template>

        <template v-else-if="(section.section_type ?? 'courses') === 'external_links'">
            <div>
                <label class="mb-1 flex items-center gap-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    <ExternalLink class="h-3.5 w-3.5" /> URL
                </label>
                <input
                    :value="editingExternalUrl"
                    type="url"
                    :class="inputClass"
                    class="w-full"
                    placeholder="https://..."
                    @input="emit('update:editingExternalUrl', $event.target.value)"
                />
            </div>
        </template>

        <template v-else>
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Liberação</label>
                <div class="grid gap-2">
                    <select
                        :value="editingReleaseMode"
                        :class="inputClass"
                        class="w-full"
                        @change="emit('update:editingReleaseMode', $event.target.value)"
                    >
                        <option value="none">Imediata</option>
                        <option value="days">Após X dias</option>
                        <option value="date">Na data</option>
                    </select>
                    <input
                        v-if="editingReleaseMode === 'days'"
                        :value="editingReleaseAfterDays"
                        type="number"
                        min="1"
                        :class="inputClass"
                        class="w-full"
                        @input="emit('update:editingReleaseAfterDays', $event.target.value)"
                    />
                    <input
                        v-else-if="editingReleaseMode === 'date'"
                        :value="editingReleaseAtDate"
                        type="date"
                        :class="inputClass"
                        class="w-full"
                        @input="emit('update:editingReleaseAtDate', $event.target.value)"
                    />
                </div>
            </div>
            <div>
                <label class="mb-1 block text-xs font-medium text-zinc-600 dark:text-zinc-400">Duração do acesso</label>
                <input
                    :value="editingAccessDurationDays"
                    type="number"
                    min="1"
                    max="3650"
                    :class="inputClass"
                    class="w-full"
                    placeholder="Ilimitado"
                    @input="emit('update:editingAccessDurationDays', $event.target.value)"
                />
                <p class="mt-1 text-[10px] text-zinc-500 dark:text-zinc-400">
                    Em dias após a compra. Deixe vazio para acesso ilimitado.
                </p>
                <label class="flex items-center gap-2 text-xs font-medium text-zinc-600 dark:text-zinc-400">
                    <input
                        :checked="editingRequiresPreviousModules"
                        type="checkbox"
                        :disabled="editingReleaseDependencyOptions.length === 0"
                        class="rounded border-zinc-300 text-[var(--color-primary)] focus:ring-[var(--color-primary)]"
                        @change="emit('update:editingRequiresPreviousModules', $event.target.checked)"
                    />
                    Módulo(s) anterior(es) concluído(s)?
                </label>
                <select
                    v-if="editingRequiresPreviousModules && editingReleaseDependencyOptions.length > 0"
                    value=""
                    :class="inputClass"
                    class="mt-2 w-full"
                    @change="addDependency($event.target.value); $event.target.value = ''"
                >
                    <option value="">Adicionar módulo anterior...</option>
                    <option v-for="candidate in availableDependencyOptions" :key="candidate.id" :value="candidate.id">
                        {{ Number(candidate.id) === Number(editingReleaseDependencyOptions[0]?.id) ? 'Módulo anterior — ' : '' }}{{ candidate.title }}
                    </option>
                </select>
                <div v-if="editingRequiresPreviousModules && editingReleaseDependencyOptions.length > 0" class="mt-2 space-y-2">
                    <div v-for="dependency in editingReleaseDependencies" :key="dependency.module_id" class="flex items-center gap-2 rounded-lg border border-zinc-200 px-2 py-1.5 text-xs dark:border-zinc-700">
                        <span class="min-w-0 flex-1 truncate">{{ dependencyTitle(dependency.module_id) }}</span>
                        <label class="flex shrink-0 items-center gap-1 text-zinc-500">mín.
                            <input :value="dependency.minimum_progress_percent" type="number" min="1" max="100" class="w-12 rounded border border-zinc-300 px-1 py-0.5 text-right dark:border-zinc-600 dark:bg-zinc-800" @input="updateDependencyProgress(dependency.module_id, $event.target.value)" />%
                        </label>
                        <button type="button" class="text-red-600" @click="removeDependency(dependency.module_id)">×</button>
                    </div>
                </div>
                <p v-if="editingRequiresPreviousModules && editingReleaseDependencyOptions.length > 0" class="mt-1 text-[10px] text-zinc-500">A primeira opção é o módulo imediatamente anterior. O padrão é 100%.</p>
                <p v-else-if="editingReleaseDependencyOptions.length === 0" class="mt-1 text-[10px] text-zinc-500">Não há módulo anterior para selecionar.</p>
            </div>
        </template>

        <Toggle
            :model-value="editingShowTitleOnCover"
            label="Mostrar título na capa"
            @update:model-value="(v) => { emit('update:editingShowTitleOnCover', v); emit('set-show-title-on-cover', v); }"
        />

        <div>
            <p class="mb-2 text-xs font-medium text-zinc-600 dark:text-zinc-400">Capa do módulo</p>
            <div v-if="editingThumbnail" class="flex items-center gap-3">
                <div
                    :class="section.cover_mode === 'horizontal' ? 'aspect-video w-24' : 'aspect-[2/3] h-20 w-14'"
                    class="shrink-0 overflow-hidden rounded-lg"
                >
                    <img :src="editingThumbnail" alt="Capa" class="h-full w-full object-cover" />
                </div>
                <div class="flex flex-wrap gap-2">
                    <Button type="button" size="sm" variant="outline" :disabled="thumbnailUploading" @click="emit('pick-thumbnail')">Trocar</Button>
                    <Button type="button" size="sm" variant="ghost" class="text-red-600" :disabled="thumbnailUploading" @click="emit('remove-thumbnail')">Remover</Button>
                </div>
            </div>
            <Button v-else type="button" size="sm" variant="outline" :disabled="thumbnailUploading" @click="emit('pick-thumbnail')">
                {{ thumbnailUploading ? 'Enviando…' : 'Enviar capa' }}
            </Button>
            <p class="mt-1 text-[10px] text-zinc-500">Máx. {{ uploadLimits.image_max_mb }} MB.</p>
        </div>

        <Button size="sm" class="w-full" :disabled="saving" @click="emit('save')">
            {{ saving ? 'Salvando…' : 'Salvar módulo' }}
        </Button>
    </div>
</template>
