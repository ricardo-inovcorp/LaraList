<template>
  <Head title="Nova Tarefa" />

  <AppLayout>
    <div class="py-10">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-6">
          <Link 
            :href="route('tarefas.index')" 
            class="inline-flex items-center text-sm font-medium text-indigo-400 hover:text-indigo-300 transition-colors"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Voltar para tarefas
          </Link>
        </div>

        <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg overflow-hidden">
          <div class="px-6 py-5 border-b border-gray-200 dark:border-white/10">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
              <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">
                Nova Tarefa
              </span>
            </h2>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Crie uma nova tarefa e organize seu trabalho.</p>
          </div>

          <div class="p-6">
            <form @submit.prevent="form.post(route('tarefas.store'))">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Título -->
                <div class="col-span-1 md:col-span-2">
                  <InputLabel for="titulo" value="Título" class="text-gray-900 dark:text-white" required />
                  <TextInput
                    id="titulo"
                    type="text"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
                    v-model="form.titulo"
                    required
                    autofocus
                  />
                  <InputError class="mt-2" :message="form.errors.titulo" />
                </div>

                <!-- Descrição -->
                <div class="col-span-1 md:col-span-2">
                  <InputLabel for="descricao" value="Descrição" class="text-gray-900 dark:text-white"/>
                  <textarea
                    id="descricao"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
                    v-model="form.descricao"
                    rows="4"
                  ></textarea>
                  <InputError class="mt-2" :message="form.errors.descricao" />
                </div>

                <!-- Estado -->
                <div>
                  <InputLabel for="estado" value="Estado" class="text-gray-900 dark:text-white" required />
                  <SelectInput
                    id="estado"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
                    v-model="form.estado"
                    required
                  >
                    <option value="pendente">Pendente</option>
                    <option value="em_progresso">Em Progresso</option>
                    <option value="concluida">Concluída</option>
                    <option value="cancelada">Cancelada</option>
                  </SelectInput>
                  <InputError class="mt-2" :message="form.errors.estado" />
                </div>

                <!-- Prioridade -->
                <div>
                  <InputLabel for="prioridade" value="Prioridade" class="text-gray-900 dark:text-white" required />
                  <SelectInput
                    id="prioridade"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
                    v-model="form.prioridade"
                    required
                  >
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                  </SelectInput>
                  <InputError class="mt-2" :message="form.errors.prioridade" />
                </div>

                <!-- Categoria -->
                <div>
                  <InputLabel for="categoria_id" value="Categoria" class="text-gray-900 dark:text-white" />
                  <div class="flex space-x-2">
                    <SelectInput
                      id="categoria_id"
                      class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
                      v-model="form.categoria_id"
                    >
                      <option :value="null">Sem categoria</option>
                      <option v-for="categoria in categoriasList" :key="categoria.id" :value="categoria.id">
                        {{ categoria.nome }}
                      </option>
                    </SelectInput>
                    <button
                      type="button"
                      @click="novaCategoria.modal = true"
                      class="mt-1 inline-flex items-center justify-center px-3 py-2 bg-indigo-50 dark:bg-indigo-600/30 border border-indigo-200 dark:border-indigo-500/30 text-indigo-600 dark:text-indigo-400 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-600/50 transition-colors"
                    >
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                      </svg>
                    </button>
                  </div>
                  <InputError class="mt-2" :message="form.errors.categoria_id" />
                </div>

                <!-- Data de Conclusão -->
                <div>
                  <InputLabel for="data_conclusao" value="Data de Conclusão" class="text-gray-900 dark:text-white" />
                  <TextInput
                    id="data_conclusao"
                    type="datetime-local"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
                    v-model="form.data_conclusao"
                  />
                  <InputError class="mt-2" :message="form.errors.data_conclusao" />
                </div>
              </div>

              <div class="flex justify-end mt-6 space-x-3">
                <Link
                  :href="route('tarefas.index')"
                  class="inline-flex items-center px-4 py-2 bg-white dark:bg-white/5 border border-gray-300 dark:border-white/10 rounded-md font-semibold text-xs text-gray-700 dark:text-white uppercase tracking-widest hover:bg-gray-50 dark:hover:bg-white/10 transition ease-in-out duration-150"
                >
                  Cancelar
                </Link>
                <PrimaryButton
                  :class="{ 'opacity-25': form.processing }"
                  :disabled="form.processing"
                  class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700"
                >
                  Criar Tarefa
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal Nova Categoria -->
    <Modal :show="novaCategoria.modal" @close="novaCategoria.modal = false">
      <div class="p-6 bg-white dark:bg-gray-900 rounded-lg backdrop-blur-lg bg-opacity-80 border border-gray-200 dark:border-white/10">
        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4 tracking-tight">Adicionar Nova Categoria</h2>
        <p class="mb-4 text-sm text-gray-600 dark:text-gray-400">Adicione uma nova categoria para melhor organizar suas tarefas.</p>
        
        <div class="space-y-4">
          <div>
            <InputLabel for="nova_categoria_nome" value="Nome da Categoria" />
            <TextInput
              id="nova_categoria_nome"
              type="text"
              class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
              v-model="novaCategoria.nome"
              ref="novaCategoriaInput"
              @keyup.enter="criarCategoria"
            />
            <InputError class="mt-2" :message="novaCategoria.erro" />
          </div>
        </div>
        
        <div class="flex justify-end mt-6 space-x-3">
          <SecondaryButton @click="novaCategoria.modal = false">
            Cancelar
          </SecondaryButton>
          <PrimaryButton 
            @click="criarCategoria"
            :disabled="novaCategoria.processando"
            class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700"
          >
            Criar
          </PrimaryButton>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref, nextTick, watch } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import Modal from '@/components/Modal.vue';
import axios from 'axios';

const props = defineProps({
  categorias: {
    type: Array,
    default: () => [],
  },
});

// Lista reativa de categorias
const categoriasList = ref([...props.categorias]);

// Form para criação da tarefa
const form = useForm({
  titulo: '',
  descricao: '',
  estado: 'pendente',
  prioridade: 'media',
  categoria_id: null,
  data_conclusao: '',
});

// Form para nova categoria
const novaCategoriaInput = ref(null);
const novaCategoria = ref({
  modal: false,
  nome: '',
  erro: '',
  processando: false,
});

// Método para criar nova categoria
const criarCategoria = async () => {
  if (!novaCategoria.value.nome.trim()) {
    novaCategoria.value.erro = 'O nome da categoria é obrigatório';
    return;
  }

  novaCategoria.value.processando = true;
  novaCategoria.value.erro = '';

  try {
    const response = await axios.post(route('api.categorias.store'), {
      nome: novaCategoria.value.nome
    });

    // Adicionar a nova categoria à lista reativa e selecionar
    categoriasList.value.push(response.data);
    form.categoria_id = response.data.id;
    
    // Fechar modal e limpar
    novaCategoria.value.modal = false;
    novaCategoria.value.nome = '';
  } catch (error) {
    novaCategoria.value.erro = error.response?.data?.message || 'Erro ao criar categoria';
  } finally {
    novaCategoria.value.processando = false;
  }
};

// Foco no input de nova categoria quando o modal abrir
watch(() => novaCategoria.value.modal, (value) => {
  if (value) {
    nextTick(() => {
      novaCategoriaInput.value.focus();
    });
  }
});
</script> 