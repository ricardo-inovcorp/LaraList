<template>
    <Head title="Assinatura" />
    <AppLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Gerenciar Assinatura</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="$page.props.flash && $page.props.flash.message" class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    {{ $page.props.flash.message }}
                </div>
                <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
            <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">
              LaraList Pro
            </span>
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gerencie suas tarefas e organize seu trabalho.</p>
        </div>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 sm:mb-0 tracking-tight">
            <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">Minha Subscrição</span>
          </h1>
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mt-4">
                    <div class="p-6 text-gray-900 dark:text-gray-100 space-y-6">
                        <!-- Status da Assinatura -->
                        <div v-if="subscription" class="p-4 bg-gray-100 dark:bg-gray-700 rounded">
                            <h3 class="text-lg font-medium mb-2">Status da Subscrição</h3>
                            <div class="space-y-2">
                                <p>
                                    <span class="font-medium">Plano:</span> 
                                    {{ subscription.plan_type === 'free' ? 'Avaliação Gratuita' : 'Premium' }}
                                </p>
                                
                                <p v-if="onTrial && displayTrialDays > 0">
                                    <span class="font-medium">Período de avaliação:</span> 
                                    Termina em {{ displayTrialDays }} dias
                                </p>
                                
                                <p v-else-if="onTrial && displayTrialDays <= 0">
                                    <span class="font-medium">Período de avaliação:</span> 
                                    <span class="text-red-500">Expirado</span>
                                </p>
                                
                                <p v-if="subscription.subscription_ends_at && !onTrial">
                                    <span class="font-medium">Assinatura válida até:</span> 
                                    {{ formatDate(subscription.subscription_ends_at) }}
                                </p>
                                
                                <div class="mt-4">
                                    <span 
                                        class="px-2 py-1 text-xs rounded" 
                                        :class="subscription.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ subscription.is_active ? 'ATIVA' : 'INATIVA' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Botão para começar período de avaliação gratuita - apenas se não tiver assinatura -->
                        <div v-if="!subscription" class="mt-6">
                            <h3 class="text-lg font-medium mb-4">Comece com 14 dias grátis!</h3>
                            <p class="mb-4">
                                Experimente todas as funcionalidades sem compromisso por 14 dias.
                            </p>
                            <form @submit.prevent="startFreeTrial">
                                <PrimaryButton type="submit">Começar período gratuito</PrimaryButton>
                            </form>
                        </div>

                        <!-- Atualização da assinatura para premium - apenas em casos específicos -->
                        <div v-if="shouldShowUpgrade" class="mt-6 border-t pt-6">
                            <h3 class="text-lg font-medium mb-4">Atualize para o plano Premium</h3>
                            <p class="mb-4">
                                Obtenha acesso a todas as funcionalidades avançadas.
                            </p>
                            
                            <div class="p-4 bg-gray-100 dark:bg-gray-700 rounded mb-4">
                                <h4 class="font-medium mb-2">Plano Premium</h4>
                                <ul class="list-disc pl-5 mb-4">
                                    <li>Acesso a todas as funcionalidades</li>
                                    <li>Sem limites de uso</li>
                                    <li>Suporte prioritário</li>
                                </ul>
                                <p class="font-medium"> €5,90/mês</p>
                            </div>
                            
                            <div id="paypal-button-container" class="mt-4"></div>
                        </div>

                        <!-- Cancelar assinatura - apenas para plano premium ativo -->
                        <div v-if="hasActiveSubscription && subscription && subscription.plan_type === 'premium'" class="mt-6 border-t pt-6">
                            <h3 class="text-lg font-medium mb-4">Gerenciar Assinatura</h3>
                            <form @submit.prevent="cancelSubscription">
                                <SecondaryButton type="submit" class="bg-red-500 hover:bg-red-600 text-white">
                                    Cancelar assinatura
                                </SecondaryButton>
                            </form>
                        </div>
                        
                        <!-- Aviso de expiração iminente -->
                        <div v-if="onTrial && displayTrialDays > 0 && displayTrialDays <= 3" class="mt-6 p-4 bg-yellow-100 text-yellow-800 rounded">
                            <p class="font-medium">Seu período de avaliação está terminando!</p>
                            <p class="mt-1">Atualize para o plano Premium para continuar tendo acesso a todas as funcionalidades.</p>
                        </div>
                        
                        <!-- Aviso de expiração do período de avaliação -->
                        <div v-if="onTrial && displayTrialDays <= 0" class="mt-6 p-4 bg-red-100 text-red-800 rounded">
                            <p class="font-medium">Seu período de avaliação expirou!</p>
                            <p class="mt-1">Atualize para o plano Premium para restaurar o acesso a todas as funcionalidades.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';
import SecondaryButton from '@/components/SecondaryButton.vue';
import { router } from '@inertiajs/vue3';

// Props da página
const props = defineProps({
    subscription: Object,
    onTrial: Boolean,
    hasActiveSubscription: Boolean,
    trialDaysLeft: Number
});

// Calcular dias restantes do período de avaliação
const displayTrialDays = computed(() => {
    if (!props.subscription || !props.onTrial) return 0;
    return Math.floor(props.trialDaysLeft) + 1;
});

// Verificar se devemos mostrar opção de upgrade
const shouldShowUpgrade = computed(() => {
    if (!props.subscription) return false;
    
    // Mostrar para planos free
    if (props.subscription.plan_type === 'free') return true;
    
    // Mostrar para usuários em trial (ativo ou expirado)
    if (props.onTrial) return true;
    
    // Mostrar para assinaturas expiradas ou inativas
    if (!props.hasActiveSubscription) return true;
    
    // Não mostrar em outros casos (ex: premium ativo)
    return false;
});

// Formatar data
const formatDate = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
};

// Iniciar período de avaliação gratuita
const startFreeTrial = () => {
    router.post(route('subscription.start-trial'));
};

// Cancelar assinatura
const cancelSubscription = () => {
    if (confirm('Tem certeza que deseja cancelar sua assinatura?')) {
        router.post(route('subscription.cancel'));
    }
};

// Integração com PayPal
onMounted(() => {
    if (window.paypal && shouldShowUpgrade.value) {
        window.paypal.Buttons({
            createOrder: function(data, actions) {
                return actions.order.create({
                    purchase_units: [{
                        amount: {
                            value: '29.90',
                            currency_code: 'BRL'
                        },
                        description: 'Assinatura mensal Premium'
                    }]
                });
            },
            onApprove: function(data, actions) {
                return actions.order.capture().then(function(details) {
                    // Enviar os detalhes do pagamento para o backend
                    router.post(route('subscription.payment'), {
                        payment_id: details.id,
                        plan_type: 'premium'
                    });
                });
            }
        }).render('#paypal-button-container');
    }
});
</script> 