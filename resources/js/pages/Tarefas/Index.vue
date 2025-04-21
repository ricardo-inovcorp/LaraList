<template>
  <Head title="Gerir Tarefas" />

  <AppLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <h1 class="text-2xl text-white font-bold mb-6">Gestão de Tarefas</h1>
        <div class="overflow-hidden bg-gray-800 shadow-sm sm:rounded-lg">
          <div class="p-6 bg-gray-800 border-b border-gray-700 text-white">
            
  
            <div class="flex justify-between mb-6">
              <div class="flex flex-col gap-2 md:flex-row md:items-end md:gap-4">
                <div class="flex flex-col">
                  <label class="mb-1 text-lg font-medium text-white mb-2">Filtrar por</label>

                  <select
                    id="filtro-estado"
                    v-model="filtros.estado"
                    @change="filtrarTarefas"
                    class="rounded-md border-gray-400 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-white h-10 bg-gray-700"
                  >
                    <option value="">Todos os estados</option>
                    <option value="pendente">Pendente</option>
                    <option value="em_progresso">Em Progresso</option>
                    <option value="concluida">Concluída</option>
                    <option value="cancelada">Cancelada</option>
                  </select>
                </div>

                <div class="flex flex-col">
                  <select
                    id="filtro-prioridade"
                    v-model="filtros.prioridade"
                    @change="filtrarTarefas"
                    class="rounded-md border-gray-400 focus:border-indigo-500 focus:ring-indigo-500 shadow-sm text-white h-10 bg-gray-700"
                  >
                    <option value="">Todas as prioridades</option>
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                  </select>
                </div>

                <button
                  @click="limparFiltros"
                  class="px-4 py-2 text-sm text-gray-100 bg-gray-600 border border-gray-500 rounded-md hover:bg-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 h-10"
                  :class="{ 'opacity-50 cursor-not-allowed': !filtrosAplicados }"
                  :disabled="!filtrosAplicados"
                >
                  Limpar Filtros
                </button>
              </div>

              <div class="flex items-end">
                <Link
                  :href="route('tarefas.create')"
                  class="inline-flex items-center px-4 py-2 text-xs font-semibold tracking-widest text-white uppercase transition bg-gray-700 border border-transparent rounded-md hover:bg-gray-600 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring focus:ring-gray-300"
                >
                  Nova Tarefa
                </Link>
              </div>
            </div>

            <!-- Flash Message - Corrigido para verificar se flash e flash.mensagem existem -->
            <div v-if="$page.props.flash && $page.props.flash.mensagem" class="p-4 mb-4 text-sm text-green-100 bg-green-800 rounded-lg">
              {{ $page.props.flash.mensagem }}
            </div>

            <div v-if="!tarefas || !tarefas.data" class="p-4 mb-4 text-sm text-yellow-100 bg-yellow-800 rounded-lg">
              Carregando dados ou nenhuma tarefa disponível...
            </div>

            <div v-else class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-600">
                <thead class="bg-gray-700">
                  <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Título
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Estado
                    </th>
                    <th 
                      scope="col" 
                      class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600"
                      @click="alternarOrdenacao('prioridade')"
                    >
                      Prioridade
                      <span v-if="ordemAtiva.campo === 'prioridade'" class="ml-1">
                        {{ ordemAtiva.direcao === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Categoria
                    </th>
                    <th 
                      scope="col" 
                      class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600"
                      @click="alternarOrdenacao('data_conclusao')"
                    >
                      Conclusão Prevista
                      <span v-if="ordemAtiva.campo === 'data_conclusao'" class="ml-1">
                        {{ ordemAtiva.direcao === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                    <th 
                      scope="col" 
                      class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider cursor-pointer hover:bg-gray-600"
                      @click="alternarOrdenacao('created_at')"
                    >
                      Criação
                      <span v-if="ordemAtiva.campo === 'created_at'" class="ml-1">
                        {{ ordemAtiva.direcao === 'asc' ? '↑' : '↓' }}
                      </span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-300 uppercase tracking-wider">
                      Ações
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-gray-800 divide-y divide-gray-700">
                  <tr v-for="tarefa in tarefas.data" :key="tarefa.id">
                    <td class="px-6 py-4 whitespace-nowrap">
                      <div class="text-sm font-medium text-white">{{ tarefa.titulo }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                          'bg-yellow-100 text-yellow-800': tarefa.estado === 'pendente',
                          'bg-blue-100 text-blue-800': tarefa.estado === 'em_progresso',
                          'bg-green-100 text-green-800': tarefa.estado === 'concluida',
                          'bg-red-100 text-red-800': tarefa.estado === 'cancelada',
                        }"
                      >
                        {{ formatarEstado(tarefa.estado) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                        :class="{
                          'bg-gray-100 text-gray-800': tarefa.prioridade === 'baixa',
                          'bg-blue-100 text-blue-800': tarefa.prioridade === 'media',
                          'bg-orange-100 text-orange-800': tarefa.prioridade === 'alta',
                          'bg-red-100 text-red-800': tarefa.prioridade === 'urgente',
                        }"
                      >
                        {{ formatarPrioridade(tarefa.prioridade) }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                      {{ tarefa.categoria || '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                      {{ tarefa.data_conclusao ? formatarData(tarefa.data_conclusao) : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-300">
                      {{ tarefa.created_at ? formatarData(tarefa.created_at) : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                      <div class="flex space-x-2">
                        <Link
                          :href="route('tarefas.show', tarefa.id)"
                          class="text-indigo-400 hover:text-indigo-300"
                        >
                          Ver
                        </Link>
                        <Link
                          :href="route('tarefas.edit', tarefa.id)"
                          class="text-indigo-400 hover:text-indigo-300"
                        >
                          Editar
                        </Link>
                        <Link
                          :href="route('tarefas.toggle-concluida', tarefa.id)"
                          method="patch"
                          as="button"
                          class="text-green-400 hover:text-green-300"
                        >
                          {{ tarefa.concluida ? 'Reabrir' : 'Concluir' }}
                        </Link>
                        <Link
                          :href="route('tarefas.destroy', tarefa.id)"
                          method="delete"
                          as="button"
                          class="text-red-400 hover:text-red-300"
                          @click="confirmarExclusao"
                        >
                          Eliminar
                        </Link>
                      </div>
                    </td>
                  </tr>
                  <tr v-if="tarefas.data.length === 0">
                    <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-300">
                      Nenhuma tarefa encontrada.
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div v-if="tarefas && tarefas.links" class="mt-4">
              <Pagination :links="tarefas.links" class="pagination-dark" />
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Pagination from '@/components/Pagination.vue';

const props = defineProps({
  tarefas: Object,
  filtros: Object,
});

const filtros = ref({
  estado: props.filtros.estado || '',
  prioridade: props.filtros.prioridade || '',
  categoria: props.filtros.categoria || '',
});

// Inicializa a ordem ativa com base nos parâmetros da URL
const ordemAtiva = ref((() => {
  const ordem = props.filtros.ordem || 'created_at_desc';
  
  // Parse da string de ordenação (ex: prioridade_asc -> campo: prioridade, direcao: asc)
  const [campo, direcao] = ordem.split('_');
  
  return {
    campo: campo || 'created_at',
    direcao: direcao || 'desc',
  };
})());

// Verificar se algum filtro está aplicado
const filtrosAplicados = computed(() => {
  return filtros.value.estado !== '' || 
         filtros.value.prioridade !== '' || 
         filtros.value.categoria !== '' ||
         (ordemAtiva.value.campo !== 'created_at' || ordemAtiva.value.direcao !== 'desc');
});

// Limpar todos os filtros
const limparFiltros = () => {
  filtros.value.estado = '';
  filtros.value.prioridade = '';
  filtros.value.categoria = '';
  ordemAtiva.value.campo = 'created_at';
  ordemAtiva.value.direcao = 'desc';
  
  filtrarTarefas();
};

const filtrarTarefas = () => {
  const ordem = `${ordemAtiva.value.campo}_${ordemAtiva.value.direcao}`;
  
  router.get(
    route('tarefas.index'),
    {
      estado: filtros.value.estado,
      prioridade: filtros.value.prioridade,
      categoria: filtros.value.categoria,
      ordem: ordem,
    },
    {
      preserveState: true,
      replace: true,
    }
  );
};

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
  return new Date(data).toLocaleDateString('pt-PT');
};

const confirmarExclusao = (e) => {
  if (!confirm('Tem a certeza de que deseja eliminar esta tarefa?')) {
    e.preventDefault();
  }
};

const alternarOrdenacao = (campo) => {
  if (ordemAtiva.value.campo === campo) {
    ordemAtiva.value.direcao = ordemAtiva.value.direcao === 'asc' ? 'desc' : 'asc';
  } else {
    ordemAtiva.value.campo = campo;
    ordemAtiva.value.direcao = 'asc';
  }
  filtrarTarefas();
};
</script>

<!-- Fim do arquivo --> 