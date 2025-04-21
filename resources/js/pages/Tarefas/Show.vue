<template>
  <Head title="Detalhes da Tarefa" />

  <AppLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
          <div class="p-6 bg-white border-b border-gray-200">
            <div class="mb-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-2xl font-bold">{{ tarefa.titulo }}</h3>
                <div class="flex space-x-2">
                  <Link
                    :href="route('tarefas.edit', tarefa.id)"
                    class="px-4 py-2 text-sm text-white bg-indigo-600 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                  >
                    Editar
                  </Link>
                  <Link
                    :href="route('tarefas.toggle-concluida', tarefa.id)"
                    method="patch"
                    as="button"
                    class="px-4 py-2 text-sm text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                  >
                    {{ tarefa.concluida ? 'Reabrir' : 'Concluir' }}
                  </Link>
                </div>
              </div>

              <div class="flex flex-wrap gap-3 mb-4">
                <span
                  class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-yellow-100 text-yellow-800': tarefa.estado === 'pendente',
                    'bg-blue-100 text-blue-800': tarefa.estado === 'em_progresso',
                    'bg-green-100 text-green-800': tarefa.estado === 'concluida',
                    'bg-red-100 text-red-800': tarefa.estado === 'cancelada',
                  }"
                >
                  {{ formatarEstado(tarefa.estado) }}
                </span>

                <span
                  class="px-3 py-1 text-sm font-semibold rounded-full"
                  :class="{
                    'bg-gray-100 text-gray-800': tarefa.prioridade === 'baixa',
                    'bg-blue-100 text-blue-800': tarefa.prioridade === 'media',
                    'bg-orange-100 text-orange-800': tarefa.prioridade === 'alta',
                    'bg-red-100 text-red-800': tarefa.prioridade === 'urgente',
                  }"
                >
                  {{ formatarPrioridade(tarefa.prioridade) }}
                </span>

                <span v-if="tarefa.categoria" class="px-3 py-1 text-sm font-semibold bg-purple-100 text-purple-800 rounded-full">
                  {{ tarefa.categoria }}
                </span>
              </div>
            </div>

            <div class="mb-6">
              <h4 class="text-lg font-semibold mb-2">Descrição</h4>
              <div class="whitespace-pre-wrap text-gray-700">{{ tarefa.descricao || 'Sem descrição' }}</div>
            </div>

            <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2">
              <div>
                <h4 class="text-lg font-semibold mb-2">Data de Criação</h4>
                <div class="text-gray-700">{{ formatarData(tarefa.created_at) }}</div>
              </div>
              <div>
                <h4 class="text-lg font-semibold mb-2">Data de Conclusão Prevista</h4>
                <div class="text-gray-700">{{ tarefa.data_conclusao ? formatarData(tarefa.data_conclusao) : 'Não definida' }}</div>
              </div>
            </div>

            <div class="border-t pt-4 mt-6">
              <div class="flex justify-between">
                <Link
                  :href="route('tarefas.index')"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  Voltar à lista
                </Link>
                <Link
                  :href="route('tarefas.destroy', tarefa.id)"
                  method="delete"
                  as="button"
                  class="text-red-600 hover:text-red-900"
                  @click="confirmarExclusao"
                >
                  Eliminar Tarefa
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';

const props = defineProps({
  tarefa: Object,
});

const formatarEstado = (estado) => {
  const estados = {
    pendente: 'Pendente',
    em_progresso: 'Em Progresso',
    concluida: 'Concluída',
    cancelada: 'Cancelada',
  };
  return estados[estado] || estado;
};

const formatarPrioridade = (prioridade) => {
  const prioridades = {
    baixa: 'Baixa',
    media: 'Média',
    alta: 'Alta',
    urgente: 'Urgente',
  };
  return prioridades[prioridade] || prioridade;
};

const formatarData = (data) => {
  return new Date(data).toLocaleString('pt-PT');
};

const confirmarExclusao = (e) => {
  if (!confirm('Tem a certeza de que deseja eliminar esta tarefa?')) {
    e.preventDefault();
  }
};
</script> 