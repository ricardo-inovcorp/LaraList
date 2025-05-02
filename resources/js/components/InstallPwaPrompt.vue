<template>
  <transition name="slide-up">
    <div
      v-if="showInstallPrompt"
      class="fixed bottom-0 left-0 right-0 p-4 bg-gradient-to-r from-blue-600 to-indigo-700 text-white shadow-xl z-50 rounded-t-2xl"
    >
      <div class="container mx-auto flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="hidden sm:flex bg-white/20 rounded-full p-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
          </div>
          <div>
            <h3 class="font-bold text-lg">Instale o LaraList em seu dispositivo</h3>
            <p class="text-white/80 text-sm">Acesse offline e receba notificações</p>
          </div>
        </div>
        <div class="flex items-center space-x-3">
          <button
            @click="dismissPrompt"
            class="px-4 py-2 text-sm rounded-full border border-white/30 hover:bg-white/10 transition-colors duration-200"
          >
            Agora não
          </button>
          <button
            @click="installPwa"
            class="px-4 py-2 text-sm bg-white text-indigo-700 rounded-full font-medium hover:bg-white/90 transition-colors duration-200 shadow-lg flex items-center"
          >
            <span>Instalar</span>
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 ml-1">
              <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </transition>
</template>

<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount } from 'vue';

const showInstallPrompt = ref(false);
const deferredPrompt = ref<any>(null);

// Verificar se o app já está instalado
const isAppInstalled = () => {
  return window.matchMedia('(display-mode: standalone)').matches || 
         window.navigator.standalone === true;
};

// Verificar se o usuário recusou a instalação recentemente
const hasRecentlyDismissed = () => {
  const lastDismissed = localStorage.getItem('pwa-install-dismissed');
  if (!lastDismissed) return false;
  
  const dismissedTime = parseInt(lastDismissed);
  const now = new Date().getTime();
  
  // Se a última recusa foi há menos de 3 dias
  return now - dismissedTime < 3 * 24 * 60 * 60 * 1000;
};

// Manipular o evento beforeinstallprompt
const handleBeforeInstallPrompt = (e: Event) => {
  e.preventDefault();
  deferredPrompt.value = e;
  
  // Mostrar o prompt apenas se o app não estiver instalado e não foi recusado recentemente
  if (!isAppInstalled() && !hasRecentlyDismissed()) {
    // Pequeno delay para uma melhor experiência de usuário
    setTimeout(() => {
      showInstallPrompt.value = true;
    }, 2000);
  }
};

// Instalar o PWA
const installPwa = async () => {
  if (!deferredPrompt.value) return;
  
  // Mostrar o prompt nativo de instalação
  deferredPrompt.value.prompt();
  
  // Esperar pela resposta do usuário
  const choiceResult = await deferredPrompt.value.userChoice;
  
  // Limpar o prompt após o uso
  deferredPrompt.value = null;
  showInstallPrompt.value = false;
  
  // Registrar a instalação se aceita
  if (choiceResult.outcome === 'accepted') {
    console.log('Usuário aceitou a instalação do PWA');
    // Você pode adicionar analytics aqui
  }
};

// Fechar o prompt sem instalar
const dismissPrompt = () => {
  showInstallPrompt.value = false;
  localStorage.setItem('pwa-install-dismissed', new Date().getTime().toString());
};

onMounted(() => {
  // Adicionar listener para o evento beforeinstallprompt
  window.addEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
  
  // Verificar se o app não está instalado e não foi recusado recentemente
  if (!isAppInstalled() && !hasRecentlyDismissed()) {
    // O prompt pode já ter sido acionado antes do componente ser montado
    if (deferredPrompt.value) {
      setTimeout(() => {
        showInstallPrompt.value = true;
      }, 2000);
    }
  }
});

onBeforeUnmount(() => {
  // Remover o listener ao desmontar
  window.removeEventListener('beforeinstallprompt', handleBeforeInstallPrompt);
});
</script>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.4s ease, opacity 0.4s ease;
}

.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style> 