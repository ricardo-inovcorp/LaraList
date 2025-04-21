<template>
  <Head title="Nova Tarefa" />

  <AppLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-gray-800 shadow-sm sm:rounded-lg">
          <div class="p-6 bg-gray-800 border-b border-gray-700 text-white">
            <h1 class="text-2xl font-bold mb-6 text-white">Nova Tarefa</h1>
            <form @submit.prevent="submeter">
              <div class="mb-4">
                <InputLabel for="titulo" value="Título" class="text-white" />
                <TextInput
                  id="titulo"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.titulo"
                  required
                  autofocus
                />
                <InputError class="mt-2" :message="form.errors.titulo" />
              </div>

              <div class="mb-4">
                <InputLabel for="descricao" value="Descrição" class="text-white" />
                <TextArea
                  id="descricao"
                  class="mt-1 block w-full"
                  v-model="form.descricao"
                  rows="5"
                />
                <InputError class="mt-2" :message="form.errors.descricao" />
              </div>

              <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <InputLabel for="estado" value="Estado" class="text-white" />
                  <SelectInput
                    id="estado"
                    class="mt-1 block w-full"
                    v-model="form.estado"
                  >
                    <option value="pendente">Pendente</option>
                    <option value="em_progresso">Em Progresso</option>
                    <option value="concluida">Concluída</option>
                    <option value="cancelada">Cancelada</option>
                  </SelectInput>
                  <InputError class="mt-2" :message="form.errors.estado" />
                </div>

                <div>
                  <InputLabel for="prioridade" value="Prioridade" class="text-white" />
                  <SelectInput
                    id="prioridade"
                    class="mt-1 block w-full"
                    v-model="form.prioridade"
                  >
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                  </SelectInput>
                  <InputError class="mt-2" :message="form.errors.prioridade" />
                </div>
              </div>

              <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <InputLabel for="categoria" value="Categoria" class="text-white" />
                  <TextInput
                    id="categoria"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.categoria"
                  />
                  <InputError class="mt-2" :message="form.errors.categoria" />
                </div>

                <div>
                  <InputLabel for="data_conclusao" value="Data de Conclusão Prevista" class="text-white" />
                  <TextInput
                    id="data_conclusao"
                    type="datetime-local"
                    class="mt-1 block w-full"
                    v-model="form.data_conclusao"
                  />
                  <InputError class="mt-2" :message="form.errors.data_conclusao" />
                </div>
              </div>

              <div class="flex items-center justify-end mt-6">
                <Link
                  :href="route('tarefas.index')"
                  class="px-4 py-2 text-sm text-gray-100 bg-gray-600 border border-gray-500 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Cancelar
                </Link>
                <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Guardar
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import SelectInput from '@/components/SelectInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';

const form = useForm({
  titulo: '',
  descricao: '',
  estado: 'pendente',
  prioridade: 'media',
  categoria: '',
  data_conclusao: '',
});

const submeter = () => {
  form.post(route('tarefas.store'));
};
</script> 