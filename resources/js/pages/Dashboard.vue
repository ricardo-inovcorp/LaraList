<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import PlaceholderPattern from '../components/PlaceholderPattern.vue';
import { gsap } from 'gsap';
import { Chart as ChartJS, ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title } from 'chart.js';
import { Doughnut, Line, Bar } from 'vue-chartjs';
import { Disclosure, DisclosureButton, DisclosurePanel, Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue';
import { usePage } from '@inertiajs/vue3';
import axios from 'axios';

// Registrar componentes do ChartJS
ChartJS.register(
  ArcElement, 
  Tooltip, 
  Legend, 
  CategoryScale, 
  LinearScale, 
  PointElement, 
  LineElement, 
  BarElement, 
  Title
);

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

// Estado das tarefas
const tarefas = ref({
  total: 0,
  concluidas: 0,
  pendentes: 0,
  em_progresso: 0,
  canceladas: 0,
  porPrioridade: {
    baixa: 0,
    media: 0,
    alta: 0,
    urgente: 0
  },
  porCategoria: [],
  recentesData: {
    labels: [],
    datasets: [{
      label: 'Tarefas concluídas',
      backgroundColor: 'rgba(99, 102, 241, 0.5)',
      borderColor: 'rgb(99, 102, 241)',
      data: []
    }]
  }
});

const tendenciaData = ref({
  labels: [],
  datasets: [{
    label: 'Tarefas pendentes',
    borderColor: 'rgb(59, 130, 246)',
    backgroundColor: 'rgba(59, 130, 246, 0.5)',
    data: []
  }, {
    label: 'Tarefas concluídas', 
    borderColor: 'rgb(34, 197, 94)',
    backgroundColor: 'rgba(34, 197, 94, 0.5)',
    data: []
  }]
});

const prioridadeData = ref({
  labels: ['Baixa', 'Média', 'Alta', 'Urgente'],
  datasets: [{
    label: '',
    data: [0, 0, 0, 0],
    backgroundColor: [
      'rgba(107, 114, 128, 0.7)', // Cinza para Baixa
      'rgba(59, 130, 246, 0.7)',  // Azul para Média
      'rgba(249, 115, 22, 0.7)',  // Laranja para Alta
      'rgba(239, 68, 68, 0.7)',   // Vermelho para Urgente
    ]
  }]
});

const estadoData = ref({
  labels: ['Pendentes', 'Em progresso', 'Concluídas', 'Canceladas'],
  datasets: [{
    data: [0, 0, 0, 0],
    backgroundColor: [
      'rgba(59, 130, 246, 0.7)',  // Azul para Pendentes
      'rgba(234, 179, 8, 0.7)',   // Amarelo para Em progresso
      'rgba(34, 197, 94, 0.7)',   // Verde para Concluídas
      'rgba(239, 68, 68, 0.7)',   // Vermelho para Canceladas
    ]
  }]
});

const categoriaData = ref({
  labels: [],
  datasets: [{
    label: '',
    data: [],
    backgroundColor: []
  }]
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom',
      labels: {
        usePointStyle: true,
        color: document.querySelector('html').classList.contains('dark') ? 'white' : '#333'
      }
    }
  }
};

const prioridadeOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false // Esconder a legenda completamente
    }
  }
};

const donutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        usePointStyle: true,
        color: document.querySelector('html').classList.contains('dark') ? 'white' : '#333'
      }
    }
  }
};

const categoriaOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false // Esconder a legenda completamente
    }
  }
};

const proximasTarefas = ref([]);
const tarefasVencidas = ref([]);
const isLoading = ref(true);

// Taxa de conclusão (%)
const taxaConclusao = computed(() => {
  if (tarefas.value.total === 0) return 0;
  return Math.round((tarefas.value.concluidas / tarefas.value.total) * 100);
});

// Períodos de taxa de conclusão
const taxaConclusao15Dias = ref(0);
const taxaConclusao30Dias = ref(0);
const periodoSelecionado = ref('global'); // 'global', '15dias', '30dias'

// Obter taxa de conclusão do período selecionado
const taxaPeriodoSelecionado = computed(() => {
  switch (periodoSelecionado.value) {
    case '15dias':
      return taxaConclusao15Dias.value;
    case '30dias':
      return taxaConclusao30Dias.value;
    default:
      return taxaConclusao.value;
  }
});

// Obter a cor da barra de progresso do período selecionado
const corPeriodoSelecionado = computed(() => {
  const taxa = taxaPeriodoSelecionado.value;
  if (taxa < 30) return 'bg-red-500';
  if (taxa < 70) return 'bg-yellow-500';
  return 'bg-green-500';
});

// Progress Bar Color
const progressColor = computed(() => {
  const taxa = taxaConclusao.value;
  if (taxa < 30) return 'bg-red-500';
  if (taxa < 70) return 'bg-yellow-500';
  return 'bg-green-500';
});

// Cores para barras de progresso por período
const progressColor15Dias = computed(() => {
  const taxa = taxaConclusao15Dias.value;
  if (taxa < 30) return 'bg-red-500';
  if (taxa < 70) return 'bg-yellow-500';
  return 'bg-green-500';
});

const progressColor30Dias = computed(() => {
  const taxa = taxaConclusao30Dias.value;
  if (taxa < 30) return 'bg-red-500';
  if (taxa < 70) return 'bg-yellow-500';
  return 'bg-green-500';
});

// Carrega dados de tarefas
async function carregarDados() {
  try {
    isLoading.value = true;
    const response = await axios.get(route('api.dashboard.stats'));
    const data = response.data;
    
    // Atualiza o gráfico de estados
    estadoData.value.datasets[0].data = [
      data.por_estado.pendente || 0,
      data.por_estado.em_progresso || 0,
      data.por_estado.concluida || 0,
      data.por_estado.cancelada || 0
    ];

    // Atualiza dados de tarefas por estado
    tarefas.value.concluidas = data.por_estado.concluida || 0;
    tarefas.value.pendentes = data.por_estado.pendente || 0;
    tarefas.value.em_progresso = data.por_estado.em_progresso || 0;
    tarefas.value.canceladas = data.por_estado.cancelada || 0;
    
    // Definir o total como a soma dos estados
    tarefas.value.total = tarefas.value.concluidas + tarefas.value.pendentes + 
                          tarefas.value.em_progresso + tarefas.value.canceladas;
    
    tarefas.value.porPrioridade = data.por_prioridade;

    // Atualiza o gráfico de prioridades
    prioridadeData.value.datasets[0].data = [
      data.por_prioridade.baixa || 0,
      data.por_prioridade.media || 0,
      data.por_prioridade.alta || 0,
      data.por_prioridade.urgente || 0
    ];

    // Processa dados de categorias
    const cores = [
      'rgba(99, 102, 241, 0.7)',  // Indigo
      'rgba(236, 72, 153, 0.7)',  // Pink
      'rgba(14, 165, 233, 0.7)',  // Sky
      'rgba(217, 70, 239, 0.7)',  // Fuchsia
      'rgba(245, 158, 11, 0.7)',  // Amber
      'rgba(16, 185, 129, 0.7)'   // Emerald
    ];

    categoriaData.value.labels = data.por_categoria.map(c => c.nome || 'Sem categoria');
    categoriaData.value.datasets[0].data = data.por_categoria.map(c => c.total);
    categoriaData.value.datasets[0].backgroundColor = data.por_categoria.map((_, i) => cores[i % cores.length]);

    // Processa dados de tendência
    tendenciaData.value.labels = data.tendencia.map(t => t.data);
    tendenciaData.value.datasets[0].data = data.tendencia.map(t => t.pendentes);
    tendenciaData.value.datasets[1].data = data.tendencia.map(t => t.concluidas);

    // Tarefas recentes
    tarefas.value.recentesData.labels = data.recentes.map(t => t.data);
    tarefas.value.recentesData.datasets[0].data = data.recentes.map(t => t.total);

    // Próximas tarefas e vencidas
    proximasTarefas.value = data.proximas || [];
    tarefasVencidas.value = data.vencidas || [];

    // Animar contadores quando os dados forem carregados
    animarContadores();

    // Atualizar taxas de conclusão por período
    taxaConclusao15Dias.value = data.taxa_conclusao_15_dias || 0;
    taxaConclusao30Dias.value = data.taxa_conclusao_30_dias || 0;
  } catch (error) {
    console.error('Erro ao carregar estatísticas:', error);
    isLoading.value = false;
  } finally {
    isLoading.value = false;
  }
}

// Anima contadores para efeito visual
function animarContadores() {
  const elementos = [
    { id: 'contador-total', valor: tarefas.value.total },
    { id: 'contador-concluidas', valor: tarefas.value.concluidas },
    { id: 'contador-pendentes', valor: tarefas.value.pendentes },
    { id: 'contador-progresso', valor: tarefas.value.em_progresso },
    { id: 'contador-canceladas', valor: tarefas.value.canceladas }
  ];

  elementos.forEach(elemento => {
    const el = document.getElementById(elemento.id);
    if (!el) return;

    let contador = { valor: 0 };
    gsap.to(contador, {
      valor: elemento.valor,
      duration: 2,
      ease: "power2.out",
      onUpdate: function() {
        el.textContent = Math.round(contador.valor);
      }
    });
  });

  // Animar a barra de progresso do período selecionado
  const progressBar = document.getElementById('progress-bar');
  if (progressBar) {
    gsap.fromTo(progressBar, 
      { width: '0%' }, 
      { width: `${taxaPeriodoSelecionado.value}%`, duration: 2, ease: "power2.out" }
    );
  }
}

// Classes para estilizar o card baseado na prioridade
function prioridadeClasses(prioridade) {
  switch(prioridade) {
    case 'baixa':
      return 'border-gray-300 dark:border-gray-600';
    case 'media':
      return 'border-blue-300 dark:border-blue-600';
    case 'alta':
      return 'border-orange-300 dark:border-orange-600';
    case 'urgente':
      return 'border-red-300 dark:border-red-600';
    default:
      return 'border-gray-300 dark:border-gray-600';
  }
}

// Formata data de tarefas
function formatarData(dataString) {
  if (!dataString) return '';
  const data = new Date(dataString);
  return data.toLocaleDateString('pt-BR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

// Carrega dados quando o componente montar
onMounted(() => {
  carregarDados();
});

// Reagir à mudança de período
watch(periodoSelecionado, () => {
  // Animar a barra de progresso quando mudar o período
  const progressBar = document.getElementById('progress-bar');
  if (progressBar) {
    // Reset da largura para 0 e animação para o novo valor
    gsap.fromTo(progressBar, 
      { width: '0%' }, 
      { width: `${taxaPeriodoSelecionado.value}%`, duration: 1, ease: "power2.out" }
    );
  }
});
</script>

<template>
  <Head title="Dashboard" />

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="py-8">
      <div class="mx-auto max-w-7xl">
        <!-- Cabeçalho do Dashboard -->
        <div class="mb-8">
          <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-2 tracking-tight">
            <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">
              Dashboard
            </span>
          </h1>
          <p class="text-gray-600 dark:text-gray-400">
            Visão geral das suas tarefas e atividades
          </p>
        </div>
        
        <!-- Carregando indicador -->
        <div v-if="isLoading" class="flex justify-center items-center py-12">
          <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-indigo-500"></div>
        </div>
        
        <!-- Mensagem de erro se ocorrer -->
        <div v-else-if="!tarefas.total && !isLoading" class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-red-200 dark:border-red-500/30 shadow-lg p-6 mb-6 text-center">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <h3 class="text-lg font-medium text-red-800 dark:text-red-400 mb-2">Erro ao carregar dados</h3>
          <p class="text-red-600 dark:text-red-300">Não foi possível carregar as estatísticas do dashboard. Tente novamente mais tarde.</p>
          <button @click="carregarDados" class="mt-4 px-4 py-2 bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 rounded-md hover:bg-red-200 dark:hover:bg-red-800/30 transition-colors">
            Tentar novamente
          </button>
        </div>
        
        <div v-else>
          <!-- Cards de estatísticas -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
            <!-- Total de tarefas -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6 transition-all duration-200 hover:shadow-xl">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900/30 text-indigo-600 dark:text-indigo-400 mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total de Tarefas</p>
                  <p id="contador-total" class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ tarefas.total }}</p>
                </div>
              </div>
            </div>
            
            <!-- Tarefas concluídas -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6 transition-all duration-200 hover:shadow-xl">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Concluídas</p>
                  <p id="contador-concluidas" class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ tarefas.concluidas }}</p>
                </div>
              </div>
            </div>
            
            <!-- Tarefas pendentes -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6 transition-all duration-200 hover:shadow-xl">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Pendentes</p>
                  <p id="contador-pendentes" class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ tarefas.pendentes }}</p>
                </div>
              </div>
            </div>
            
            <!-- Tarefas em progresso -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6 transition-all duration-200 hover:shadow-xl">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400 mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Em Progresso</p>
                  <p id="contador-progresso" class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ tarefas.em_progresso }}</p>
                </div>
              </div>
            </div>
            
            <!-- Tarefas canceladas -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6 transition-all duration-200 hover:shadow-xl">
              <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 mr-4">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </div>
                <div>
                  <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Canceladas</p>
                  <p id="contador-canceladas" class="text-3xl font-extrabold text-gray-900 dark:text-white">{{ tarefas.canceladas }}</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Taxa de conclusão -->
          <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6 mb-8">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Taxa de Conclusão</h2>
            
            <!-- Seletor de período -->
            <div class="flex items-center space-x-4 mb-6">
              <div class="flex items-center">
                <input
                  id="radio-global" 
                  type="radio" 
                  value="global" 
                  v-model="periodoSelecionado"
                  class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-600"
                >
                <label for="radio-global" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                  Global
                </label>
              </div>
              
              <div class="flex items-center">
                <input
                  id="radio-15dias" 
                  type="radio" 
                  value="15dias" 
                  v-model="periodoSelecionado"
                  class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-600"
                >
                <label for="radio-15dias" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                  Últimos 15 dias
                </label>
              </div>
              
              <div class="flex items-center">
                <input
                  id="radio-30dias" 
                  type="radio" 
                  value="30dias" 
                  v-model="periodoSelecionado"
                  class="w-4 h-4 text-indigo-600 border-gray-300 focus:ring-indigo-600"
                >
                <label for="radio-30dias" class="ml-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                  Últimos 30 dias
                </label>
              </div>
            </div>
            
            <!-- Taxa do período selecionado -->
            <div class="flex items-center mb-2">
              <p class="text-3xl font-extrabold text-gray-900 dark:text-white mr-2">{{ taxaPeriodoSelecionado }}%</p>
              <p class="text-sm text-gray-600 dark:text-gray-400">
                das tarefas concluídas 
                <span v-if="periodoSelecionado === 'global'">(Global)</span>
                <span v-else-if="periodoSelecionado === '15dias'">(Últimos 15 dias)</span>
                <span v-else-if="periodoSelecionado === '30dias'">(Últimos 30 dias)</span>
              </p>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
              <div id="progress-bar" class="h-2.5 rounded-full transition-all duration-1000" 
                   :class="corPeriodoSelecionado" 
                   :style="{ width: `${taxaPeriodoSelecionado}%` }"></div>
            </div>
          </div>
          
          <!-- Seção de gráficos -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Gráfico de distribuição por estado -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Distribuição por Estado</h2>
              <div class="h-72">
                <Doughnut :data="estadoData" :options="donutOptions" />
              </div>
            </div>
            
            <!-- Gráfico de distribuição por prioridade -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Distribuição por Prioridade</h2>
              <div class="h-72">
                <Bar :data="prioridadeData" :options="prioridadeOptions" />
              </div>
            </div>
          </div>
          
          <!-- Gráficos adicionais -->
          <div class="grid grid-cols-1 gap-6 mb-8">
            <!-- Tendência de tarefas -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Tendência de Tarefas</h2>
              <div class="h-80">
                <Line :data="tendenciaData" :options="chartOptions" />
              </div>
            </div>
            
            <!-- Distribuição por categoria -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Distribuição por Categoria</h2>
              <div class="h-80">
                <Bar :data="categoriaData" :options="categoriaOptions" />
              </div>
            </div>
          </div>
          
          <!-- Listas de tarefas -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Próximas tarefas -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Próximas Tarefas</h2>
              <div v-if="proximasTarefas.length === 0" class="text-center py-6 text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p>Nenhuma tarefa programada</p>
              </div>
              <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                <li v-for="tarefa in proximasTarefas" :key="tarefa.id" class="py-3">
                  <div class="flex items-center">
                    <div class="w-2 h-2 rounded-full mr-2" :class="{
                      'bg-gray-400': tarefa.prioridade === 'baixa',
                      'bg-blue-500': tarefa.prioridade === 'media',
                      'bg-orange-500': tarefa.prioridade === 'alta',
                      'bg-red-500': tarefa.prioridade === 'urgente'
                    }"></div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ tarefa.titulo }}</p>
                      <p class="text-xs text-gray-600 dark:text-gray-400">{{ formatarData(tarefa.data_conclusao) }}</p>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
            
            <!-- Tarefas vencidas -->
            <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg p-6">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Tarefas Vencidas</h2>
              <div v-if="tarefasVencidas.length === 0" class="text-center py-6 text-gray-500 dark:text-gray-400">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>Nenhuma tarefa vencida</p>
              </div>
              <ul v-else class="divide-y divide-gray-200 dark:divide-gray-700">
                <li v-for="tarefa in tarefasVencidas" :key="tarefa.id" class="py-3">
                  <div class="flex items-center">
                    <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                    <div class="flex-1">
                      <p class="text-sm font-medium text-gray-900 dark:text-white">{{ tarefa.titulo }}</p>
                      <p class="text-xs text-red-600 dark:text-red-400">Vencida: {{ formatarData(tarefa.data_conclusao) }}</p>
                    </div>
                  </div>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
