<template>
  <Head title="Tarefas" />

  <AppLayout>
    <div class="py-10">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <h1 class="text-3xl font-bold text-white mb-4 sm:mb-0">
            <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">Minhas Tarefas</span>
          </h1>
          <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-3">
            <Link 
              :href="route('tarefas.create')" 
              class="flex items-center justify-center px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-purple-500 to-indigo-600 rounded-md shadow-lg hover:from-purple-600 hover:to-indigo-700 transition-all duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
              </svg>
              Nova Tarefa
            </Link>
            
            <button 
              @click="showFilters = !showFilters" 
              class="flex items-center justify-center px-4 py-2 text-sm font-medium text-indigo-400 border border-indigo-500/30 bg-indigo-600/20 backdrop-blur-sm rounded-md shadow-lg hover:bg-indigo-600/30 transition-all duration-200"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
              </svg>
              Filtros
            </button>
          </div>
        </div>

        <!-- Mensagem de alerta/sucesso -->
        <div v-if="$page.props.flash && $page.props.flash.mensagem" 
             class="mb-6 p-4 rounded-lg border backdrop-blur-lg shadow-lg transition-all duration-500"
             :class="{
               'bg-green-500/10 border-green-500/30 text-green-400': $page.props.flash.tipo === 'sucesso',
               'bg-red-500/10 border-red-500/30 text-red-400': $page.props.flash.tipo === 'erro',
               'bg-blue-500/10 border-blue-500/30 text-blue-400': $page.props.flash.tipo === 'info'
             }"
        >
          {{ $page.props.flash.mensagem }}
        </div>

        <!-- Filtros -->
        <div v-show="showFilters" class="mb-6 backdrop-blur-lg bg-white/5 rounded-lg border border-white/10 shadow-lg overflow-hidden transition-all duration-300">
          <div class="px-6 py-4 border-b border-white/10">
            <h2 class="text-lg font-medium text-white">Filtros</h2>
          </div>
          <div class="p-6">
            <form @submit.prevent="aplicarFiltros">
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                  <InputLabel for="filtro_estado" value="Estado" />
                  <SelectInput
                    id="filtro_estado"
                    class="mt-1 block w-full bg-white/5 border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-white"
                    v-model="filtros.estado"
                  >
                    <option value="">Todos</option>
                    <option value="pendente">Pendente</option>
                    <option value="em_progresso">Em Progresso</option>
                    <option value="concluida">Concluída</option>
                    <option value="cancelada">Cancelada</option>
                  </SelectInput>
                </div>
                
                <div>
                  <InputLabel for="filtro_prioridade" value="Prioridade" />
                  <SelectInput
                    id="filtro_prioridade"
                    class="mt-1 block w-full bg-white/5 border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-white"
                    v-model="filtros.prioridade"
                  >
                    <option value="">Todas</option>
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                  </SelectInput>
                </div>
                
                <div>
                  <InputLabel for="filtro_categoria" value="Categoria" />
                  <SelectInput
                    id="filtro_categoria"
                    class="mt-1 block w-full bg-white/5 border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-white"
                    v-model="filtros.categoria_id"
                  >
                    <option value="">Todas</option>
                    <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                      {{ categoria.nome }}
                    </option>
                  </SelectInput>
                </div>
                
                <div>
                  <InputLabel for="filtro_busca" value="Busca" />
                  <TextInput
                    id="filtro_busca"
                    type="text"
                    class="mt-1 block w-full bg-white/5 border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-white"
                    v-model="filtros.busca"
                    placeholder="Título ou descrição"
                  />
                </div>
              </div>
              
              <div class="flex justify-end mt-4 space-x-3">
                <SecondaryButton @click="limparFiltros" type="button" class="border-white/10 hover:bg-white/10">
                  Limpar
                </SecondaryButton>
                <PrimaryButton class="bg-gradient-to-r from-purple-500 to-indigo-600 hover:from-purple-600 hover:to-indigo-700">
                  Aplicar
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>

        <!-- Tabela de Tarefas -->
        <div class="backdrop-blur-lg bg-white/5 rounded-lg border border-white/10 shadow-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-white/10">
            <h2 class="text-lg font-medium text-white">Lista de Tarefas</h2>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-white/10">
              <thead class="bg-white/5">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                    Título
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden md:table-cell">
                    Estado
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden md:table-cell">
                    Prioridade
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden lg:table-cell">
                    Categoria
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider hidden lg:table-cell">
                    Data Conclusão
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-300 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/10 bg-transparent">
                <tr v-for="tarefa in tarefas.data" :key="tarefa.id" 
                    class="transition-all duration-200 hover:bg-white/5 cursor-pointer"
                    :class="{
                      'bg-green-900/10': tarefa.estado === 'concluida',
                      'bg-red-900/10': tarefa.estado === 'cancelada',
                      'bg-yellow-900/10': tarefa.estado === 'em_progresso',
                    }"
                    @click="navegarParaDetalhe(tarefa.id)"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-medium text-white">
                          {{ tarefa.titulo }}
                        </div>
                        <div class="text-sm text-gray-400 mt-1">
                          {{ truncarTexto(tarefa.descricao, 20) }}
                        </div>
                        <!-- Informações visíveis apenas em mobile -->
                        <div class="mt-2 md:hidden space-y-2">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mr-2" 
                                :class="estadoBadgeClasses(tarefa.estado)">
                            {{ estadoLabel(tarefa.estado) }}
                          </span>
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                                :class="prioridadeBadgeClasses(tarefa.prioridade)">
                            {{ prioridadeLabel(tarefa.prioridade) }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                          :class="estadoBadgeClasses(tarefa.estado)">
                      {{ estadoLabel(tarefa.estado) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" 
                          :class="prioridadeBadgeClasses(tarefa.prioridade)">
                      {{ prioridadeLabel(tarefa.prioridade) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 hidden lg:table-cell">
                    <span v-if="tarefa.categoria">{{ tarefa.categoria.nome }}</span>
                    <span v-else class="text-gray-500">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300 hidden lg:table-cell">
                    <span v-if="tarefa.data_conclusao">{{ formatDate(tarefa.data_conclusao) }}</span>
                    <span v-else class="text-gray-500">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-3">
                      <Link :href="route('tarefas.edit', tarefa.id)" class="text-indigo-400 hover:text-indigo-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </Link>
                      <button @click="excluirTarefa(tarefa)" class="text-red-400 hover:text-red-300 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </button>
                    </div>
                  </td>
                </tr>
                <tr v-if="tarefas.data.length === 0">
                  <td colspan="6" class="px-6 py-12 text-center text-gray-400 text-sm">
                    <div class="flex flex-col items-center justify-center">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mb-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                      </svg>
                      <p>Nenhuma tarefa encontrada</p>
                      <Link :href="route('tarefas.create')" class="mt-4 text-indigo-400 hover:text-indigo-300 font-medium transition-colors">
                        Criar uma nova tarefa
                      </Link>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Paginação -->
        <div class="mt-6" v-if="tarefas.data.length > 0">
          <Pagination :links="tarefas.links" />
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação -->
    <Modal :show="confirmModal.show" @close="confirmModal.show = false">
      <div class="p-6 bg-gray-900 rounded-lg backdrop-blur-lg bg-opacity-80 border border-white/10">
        <h2 class="text-lg font-medium text-white mb-4">Confirmação</h2>
        <p class="text-gray-300 mb-6">
          Tem certeza que deseja excluir a tarefa "{{ confirmModal.tarefa ? confirmModal.tarefa.titulo : '' }}"?
          Esta ação não pode ser desfeita.
        </p>
        <div class="flex justify-end space-x-3">
          <SecondaryButton @click="confirmModal.show = false">
            Cancelar
          </SecondaryButton>
          <DangerButton 
            @click="confirmarExclusao" 
            :disabled="confirmModal.processando"
          >
            Excluir
          </DangerButton>
        </div>
      </div>
    </Modal>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import SelectInput from '@/components/SelectInput.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import DangerButton from '@/components/DangerButton.vue';
import Modal from '@/components/Modal.vue';
import moment from 'moment';

const props = defineProps({
  tarefas: Object,
  categorias: Array,
  filtrosAtivos: Object,
});

// Filtros
const showFilters = ref(false);
const filtros = ref({
  estado: props.filtrosAtivos?.estado || '',
  prioridade: props.filtrosAtivos?.prioridade || '',
  categoria_id: props.filtrosAtivos?.categoria_id || '',
  busca: props.filtrosAtivos?.busca || '',
});

// Modal de confirmação de exclusão
const confirmModal = ref({
  show: false,
  tarefa: null,
  processando: false,
});

// Funções para formatação e display
const formatDate = (date) => {
  return moment(date).format('DD/MM/YYYY HH:mm');
};

const truncarTexto = (texto, tamanho) => {
  if (!texto) return '';
  return texto.length > tamanho ? texto.substring(0, tamanho) + '...' : texto;
};

const navegarParaDetalhe = (id) => {
  // Impedir a navegação se o clique foi nos botões de ação
  if (event && (event.target.closest('a') || event.target.closest('button'))) {
    return;
  }
  router.get(route('tarefas.show', id));
};

const estadoLabel = (estado) => {
  const labels = {
    pendente: 'Pendente',
    em_progresso: 'Em Progresso',
    concluida: 'Concluída',
    cancelada: 'Cancelada',
  };
  return labels[estado] || estado;
};

const estadoBadgeClasses = (estado) => {
  const classes = {
    pendente: 'bg-blue-900/30 text-blue-400 border border-blue-500/30',
    em_progresso: 'bg-yellow-900/30 text-yellow-400 border border-yellow-500/30',
    concluida: 'bg-green-900/30 text-green-400 border border-green-500/30',
    cancelada: 'bg-red-900/30 text-red-400 border border-red-500/30',
  };
  return classes[estado] || '';
};

const prioridadeLabel = (prioridade) => {
  const labels = {
    baixa: 'Baixa',
    media: 'Média',
    alta: 'Alta',
    urgente: 'Urgente',
  };
  return labels[prioridade] || prioridade;
};

const prioridadeBadgeClasses = (prioridade) => {
  const classes = {
    baixa: 'bg-gray-900/30 text-gray-400 border border-gray-500/30',
    media: 'bg-blue-900/30 text-blue-400 border border-blue-500/30',
    alta: 'bg-orange-900/30 text-orange-400 border border-orange-500/30',
    urgente: 'bg-red-900/30 text-red-400 border border-red-500/30',
  };
  return classes[prioridade] || '';
};

// Ações
const aplicarFiltros = () => {
  router.get(route('tarefas.index'), filtros.value);
};

const limparFiltros = () => {
  filtros.value = {
    estado: '',
    prioridade: '',
    categoria_id: '',
    busca: '',
  };
  router.get(route('tarefas.index'));
};

const excluirTarefa = (tarefa) => {
  confirmModal.value.tarefa = tarefa;
  confirmModal.value.show = true;
  confirmModal.value.processando = false;
};

const confirmarExclusao = () => {
  confirmModal.value.processando = true;
  
  router.delete(route('tarefas.destroy', confirmModal.value.tarefa.id), {}, {
    onSuccess: () => {
      confirmModal.value.show = false;
      confirmModal.value.processando = false;
    },
  });
};
</script> 