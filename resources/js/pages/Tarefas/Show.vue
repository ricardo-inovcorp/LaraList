<template>
  <Head :title="tarefa.titulo" />

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
          <div class="px-6 py-5 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">
              <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">
                Detalhes da Tarefa
              </span>
            </h2>
            <div class="flex space-x-3">
              <Link 
                :href="route('tarefas.edit', tarefa.id)" 
                class="flex items-center px-3 py-1.5 text-md font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-indigo-600/20 border border-indigo-200 dark:border-indigo-500/30 rounded-md hover:bg-indigo-100 dark:hover:bg-indigo-600/30 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Editar
              </Link>
              <button 
                @click="excluirTarefa" 
                class="flex items-center px-3 py-1.5 text-md font-medium text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-600/20 border border-red-200 dark:border-red-500/30 rounded-md hover:bg-red-100 dark:hover:bg-red-600/30 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Excluir
              </button>
            </div>
          </div>

          <div class="divide-y divide-gray-200 dark:divide-white/10">
            <!-- Título e Status -->
            <div class="p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-start md:space-x-3">
                <h1 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-4 md:mb-0 tracking-tight">{{ tarefa.titulo }}</h1>
                <div class="flex space-x-3">
                  <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 text-sm font-medium tracking-wide" 
                        :class="estadoBadgeClasses(tarefa.estado)">
                    {{ estadoLabel(tarefa.estado) }}
                  </span>
                  <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-gray-100 text-sm font-medium tracking-wide" 
                        :class="prioridadeBadgeClasses(tarefa.prioridade)">
                    {{ prioridadeLabel(tarefa.prioridade) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Descrição -->
            <div class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3 tracking-tight">Descrição</h3>
              <div class="text-gray-700 dark:text-gray-300 whitespace-pre-line">
                {{ tarefa.descricao || 'Sem descrição' }}
              </div>
            </div>

            <!-- Detalhes adicionais -->
            <div class="p-6 bg-gray-50 dark:bg-white/5">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wider">Categoria</h4>
                  <div class="text-gray-900 dark:text-white font-medium">
                    {{ tarefa.categoria ? tarefa.categoria.nome : 'Sem categoria' }}
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wider">Data de Criação</h4>
                  <div class="text-gray-900 dark:text-white font-medium">
                    {{ formatDate(tarefa.created_at) }}
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-1 uppercase tracking-wider">Data de Conclusão</h4>
                  <div class="text-gray-900 dark:text-white font-medium">
                    {{ tarefa.data_conclusao ? formatDate(tarefa.data_conclusao) : 'Não definida' }}
                  </div>
                </div>
              </div>
            </div>

            <!-- Histórico de Atividades -->
            <div class="p-6 bg-white dark:bg-black/20">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4 tracking-tight">Histórico de Atividades</h3>
              
              <div v-if="logs.length === 0" class="text-gray-500 dark:text-gray-400 text-center py-6">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Nenhuma atividade registrada</p>
              </div>
              
              <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                <li v-for="log in logs" :key="log.id" class="py-3">
                  <div class="flex items-start">
                    <div class="mr-3 mt-1">
                      <!-- Ícone baseado no tipo de ação -->
                      <span v-if="log.tipo_acao === 'criar'" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
                        </svg>
                      </span>
                      <span v-else-if="log.tipo_acao === 'atualizar'" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                        </svg>
                      </span>
                      <span v-else-if="log.tipo_acao === 'estado'" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd" />
                        </svg>
                      </span>
                      <span v-else-if="log.tipo_acao === 'excluir'" class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                          <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                      </span>
                    </div>
                    <div class="flex-1">
                      <div class="flex items-center justify-between mb-1">
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ log.descricao }}
                        </p>
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                          {{ formatDate(log.created_at) }}
                        </span>
                      </div>
                      <p class="text-xs text-gray-600 dark:text-gray-400">
                        por {{ log.utilizador ? log.utilizador.name : 'Sistema' }}
                      </p>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <Modal :show="confirmModal.show" @close="confirmModal.show = false">
      <div class="p-6 bg-white dark:bg-gray-900 rounded-lg backdrop-blur-lg bg-opacity-80 border border-gray-200 dark:border-white/10">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirmação</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-6">
          Tem certeza que deseja excluir a tarefa "{{ tarefa.titulo }}"?
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
import Modal from '@/components/Modal.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import DangerButton from '@/components/DangerButton.vue';
import moment from 'moment';

const props = defineProps({
  tarefa: Object,
  logs: Array,
});

// Modal de confirmação
const confirmModal = ref({
  show: false,
  processando: false
});

// Formatação e Display
const formatDate = (date) => {
  return moment(date).format('DD/MM/YYYY HH:mm');
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
    pendente: 'bg-blue-100 text-blue-800 border border-blue-300 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-500/30',
    em_progresso: 'bg-yellow-100 text-yellow-800 border border-yellow-300 dark:bg-yellow-900/30 dark:text-yellow-400 dark:border-yellow-500/30',
    concluida: 'bg-green-100 text-green-800 border border-green-300 dark:bg-green-900/30 dark:text-green-400 dark:border-green-500/30',
    cancelada: 'bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/30 dark:text-red-400 dark:border-red-500/30',
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
    baixa: 'bg-gray-100 text-gray-800 border border-gray-300 dark:bg-gray-900/30 dark:text-gray-400 dark:border-gray-500/30',
    media: 'bg-blue-100 text-blue-800 border border-blue-300 dark:bg-blue-900/30 dark:text-blue-400 dark:border-blue-500/30',
    alta: 'bg-orange-100 text-orange-800 border border-orange-300 dark:bg-orange-900/30 dark:text-orange-400 dark:border-orange-500/30',
    urgente: 'bg-red-100 text-red-800 border border-red-300 dark:bg-red-900/30 dark:text-red-400 dark:border-red-500/30',
  };
  return classes[prioridade] || '';
};

// Ações
const excluirTarefa = () => {
  confirmModal.value.show = true;
  confirmModal.value.processando = false;
};

const confirmarExclusao = () => {
  confirmModal.value.processando = true;
  
  router.delete(route('tarefas.destroy', props.tarefa.id), {}, {
    onSuccess: () => {
      router.visit(route('tarefas.index'));
    },
  });
};
</script> 