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

        <div class="backdrop-blur-lg bg-white/5 rounded-lg border border-white/10 shadow-lg overflow-hidden">
          <div class="px-6 py-5 border-b border-white/10 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-white">
              <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">
                Detalhes da Tarefa
              </span>
            </h2>
            <div class="flex space-x-3">
              <Link 
                :href="route('tarefas.edit', tarefa.id)" 
                class="flex items-center px-3 py-1.5 text-xs font-medium text-indigo-400 bg-indigo-600/20 border border-indigo-500/30 rounded-md hover:bg-indigo-600/30 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                </svg>
                Editar
              </Link>
              <button 
                @click="excluirTarefa" 
                class="flex items-center px-3 py-1.5 text-xs font-medium text-red-400 bg-red-600/20 border border-red-500/30 rounded-md hover:bg-red-600/30 transition-colors"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                </svg>
                Excluir
              </button>
            </div>
          </div>

          <div class="divide-y divide-white/10">
            <!-- Título e Status -->
            <div class="p-6">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <h1 class="text-2xl font-bold text-white mb-4 md:mb-0">{{ tarefa.titulo }}</h1>
                <div class="flex space-x-3">
                  <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium" 
                        :class="estadoBadgeClasses(tarefa.estado)">
                    {{ estadoLabel(tarefa.estado) }}
                  </span>
                  <span class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium" 
                        :class="prioridadeBadgeClasses(tarefa.prioridade)">
                    {{ prioridadeLabel(tarefa.prioridade) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Descrição -->
            <div class="p-6">
              <h3 class="text-lg font-medium text-white mb-3">Descrição</h3>
              <div class="text-gray-300 whitespace-pre-line">
                {{ tarefa.descricao || 'Sem descrição' }}
              </div>
            </div>

            <!-- Detalhes adicionais -->
            <div class="p-6 bg-white/5">
              <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                  <h4 class="text-sm font-medium text-gray-400 mb-1">Categoria</h4>
                  <div class="text-white">
                    {{ tarefa.categoria ? tarefa.categoria.nome : 'Sem categoria' }}
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-400 mb-1">Data de Criação</h4>
                  <div class="text-white">
                    {{ formatDate(tarefa.created_at) }}
                  </div>
                </div>
                <div>
                  <h4 class="text-sm font-medium text-gray-400 mb-1">Data de Conclusão</h4>
                  <div class="text-white">
                    {{ tarefa.data_conclusao ? formatDate(tarefa.data_conclusao) : 'Não definida' }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal de Confirmação de Exclusão -->
    <Modal :show="confirmModal.show" @close="confirmModal.show = false">
      <div class="p-6 bg-gray-900 rounded-lg backdrop-blur-lg bg-opacity-80 border border-white/10">
        <h2 class="text-lg font-medium text-white mb-4">Confirmação</h2>
        <p class="text-gray-300 mb-6">
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