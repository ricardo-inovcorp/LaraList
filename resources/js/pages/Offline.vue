<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-900 dark:to-indigo-950 flex items-center justify-center px-4">
    <div class="max-w-md w-full p-8 bg-white dark:bg-gray-800 rounded-2xl shadow-xl">
      <div class="text-center">
        <div class="flex justify-center">
          <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-full inline-block mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m0 0l-2.829-2.829m2.829 2.829L21 21M15.536 8.464a5 5 0 010 7.072m0 0l-2.829-2.829m-4.243 2.829a4.978 4.978 0 01-1.414-2.83m-1.414 5.658a9 9 0 01-2.167-9.238m7.824 2.167a1 1 0 111.414 1.414m-1.414-1.414L3 3m8.293 8.293l1.414 1.414" />
            </svg>
          </div>
        </div>
        <h1 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-3">Você está offline</h1>
        <p class="text-gray-600 dark:text-gray-300 mb-8">
          Parece que você perdeu sua conexão com a internet. Verifique sua conexão e tente novamente.
        </p>
        
        <div class="flex flex-col space-y-3">
          <button @click="tryReconnect" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-3 px-4 rounded-lg transition-colors duration-300 flex items-center justify-center">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Tentar reconectar
          </button>
          
          <button @click="useOfflineMode" class="w-full bg-white hover:bg-gray-100 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-white font-medium py-3 px-4 border border-gray-300 dark:border-gray-600 rounded-lg transition-colors duration-300">
            Usar em modo offline
          </button>
        </div>
      </div>
      
      <div class="mt-10 pt-6 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
          <p class="text-sm text-gray-500 dark:text-gray-400">
            Funcionalidades limitadas estão disponíveis offline
          </p>
          <div class="flex space-x-1">
            <span class="h-2 w-2 bg-blue-400 rounded-full animate-pulse"></span>
            <span class="h-2 w-2 bg-blue-500 rounded-full animate-pulse delay-100"></span>
            <span class="h-2 w-2 bg-blue-600 rounded-full animate-pulse delay-200"></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const tryReconnect = () => {
  window.location.reload();
};

const useOfflineMode = () => {
  // Redirecionar para a última página visitada ou homepage
  window.location.href = '/';
};

onMounted(() => {
  // Verificar a conexão periodicamente
  const checkConnection = () => {
    if (navigator.onLine) {
      window.location.reload();
    }
  };

  // Verificar a cada 5 segundos
  const intervalId = setInterval(checkConnection, 5000);

  // Limpar intervalo ao desmontar
  return () => {
    clearInterval(intervalId);
  };
});
</script> 