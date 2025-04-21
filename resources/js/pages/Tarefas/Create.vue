<template>
  <Head title="Nova Tarefa" />

  <AppLayout>
    <div class="py-12">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <h1 class="text-2xl font-bold mb-6 text-white">Nova Tarefa</h1>
        <div class="overflow-hidden bg-gray-800 shadow-sm sm:rounded-lg">
          <div class="p-6 bg-gray-800 border-b border-gray-700 text-white">
            <form @submit.prevent="submeter">
              <div class="mb-4">
                <InputLabel for="titulo" value="Título" class="text-white" />
                <TextInput
                  id="titulo"
                  type="text"
                  class="mt-1 block w-full"
                  v-model="form.titulo"
                  required
                  autofocus
                />
                <InputError class="mt-2" :message="form.errors.titulo" />
              </div>

              <div class="mb-4">
                <InputLabel for="descricao" value="Descrição" class="text-white" />
                <TextArea
                  id="descricao"
                  class="mt-1 block w-full"
                  v-model="form.descricao"
                  rows="5"
                />
                <InputError class="mt-2" :message="form.errors.descricao" />
              </div>

              <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <InputLabel for="estado" value="Estado" class="text-white" />
                  <SelectInput
                    id="estado"
                    class="mt-1 block w-full"
                    v-model="form.estado"
                  >
                    <option value="pendente">Pendente</option>
                    <option value="em_progresso">Em Progresso</option>
                    <option value="concluida">Concluída</option>
                    <option value="cancelada">Cancelada</option>
                  </SelectInput>
                  <InputError class="mt-2" :message="form.errors.estado" />
                </div>

                <div>
                  <InputLabel for="prioridade" value="Prioridade" class="text-white" />
                  <SelectInput
                    id="prioridade"
                    class="mt-1 block w-full"
                    v-model="form.prioridade"
                  >
                    <option value="baixa">Baixa</option>
                    <option value="media">Média</option>
                    <option value="alta">Alta</option>
                    <option value="urgente">Urgente</option>
                  </SelectInput>
                  <InputError class="mt-2" :message="form.errors.prioridade" />
                </div>
              </div>

              <div class="mb-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                <div>
                  <InputLabel for="categoria" value="Categoria" class="text-white" />
                  <div class="flex gap-2">
                    <SelectInput
                      id="categoria"
                      class="mt-1 flex-grow"
                      v-model="form.categoria_id"
                      @change="selecionarCategoria"
                    >
                      <option value="">Selecione uma categoria</option>
                      <option v-for="categoria in categorias" :key="categoria.id" :value="categoria.id">
                        {{ categoria.nome }}
                      </option>
                      <option value="nova">+ Nova Categoria</option>
                    </SelectInput>
                  </div>
                  
                  <!-- Modal para adicionar nova categoria -->
                  <div v-if="mostraModalNovaCategoria" class="mt-2 p-3 bg-gray-700 rounded-md">
                    <h3 class="text-white text-sm font-medium mb-2">Nova Categoria</h3>
                    <div class="mb-2">
                      <InputLabel for="nova_categoria_nome" value="Nome" class="text-white text-xs" />
                      <TextInput
                        id="nova_categoria_nome"
                        v-model="novaCategoria.nome"
                        class="mt-1 block w-full"
                        placeholder="Digite o nome da categoria"
                      />
                    </div>
                    <div class="flex justify-end">
                      <button 
                        type="button" 
                        @click="cancelarNovaCategoria" 
                        class="px-2 py-1 text-xs text-gray-300 hover:text-white mr-2"
                      >
                        Cancelar
                      </button>
                      <button 
                        type="button" 
                        @click="salvarNovaCategoria" 
                        class="px-2 py-1 text-xs bg-indigo-600 text-white rounded hover:bg-indigo-700"
                        :disabled="!novaCategoria.nome.length"
                      >
                        Salvar
                      </button>
                    </div>
                  </div>
                  
                  <InputError class="mt-2" :message="form.errors.categoria_id" />
                </div>

                <div>
                  <InputLabel for="data_conclusao" value="Data de Conclusão Prevista" class="text-white" />
                  <TextInput
                    id="data_conclusao"
                    type="datetime-local"
                    class="mt-1 block w-full"
                    v-model="form.data_conclusao"
                  />
                  <InputError class="mt-2" :message="form.errors.data_conclusao" />
                </div>
              </div>

              <div class="flex items-center justify-end mt-6">
                <Link
                  :href="route('tarefas.index')"
                  class="px-4 py-2 text-sm text-gray-100 bg-gray-600 border border-gray-500 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                >
                  Cancelar
                </Link>
                <PrimaryButton class="ml-3" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                  Guardar
                </PrimaryButton>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import axios from 'axios';
import AppLayout from '@/layouts/AppLayout.vue';
import InputLabel from '@/components/InputLabel.vue';
import TextInput from '@/components/TextInput.vue';
import TextArea from '@/components/TextArea.vue';
import SelectInput from '@/components/SelectInput.vue';
import InputError from '@/components/InputError.vue';
import PrimaryButton from '@/components/PrimaryButton.vue';

// Lista de categorias carregadas do servidor
const categorias = ref([]);

// Estados para gerenciar a criação de nova categoria
const mostraModalNovaCategoria = ref(false);
const novaCategoria = ref({
  nome: '',
  cor: ''
});

// Formulário principal da tarefa
const form = useForm({
  titulo: '',
  descricao: '',
  estado: 'pendente',
  prioridade: 'media',
  categoria_id: '',
  data_conclusao: '',
});

// Carrega as categorias ao iniciar o componente
onMounted(async () => {
  await carregarCategorias();
});

// Função para carregar as categorias do servidor
const carregarCategorias = async () => {
  try {
    const response = await axios.get(route('categorias.index'));
    categorias.value = response.data;
  } catch (error) {
    console.error('Erro ao carregar categorias:', error);
  }
};

// Função chamada quando uma categoria é selecionada
const selecionarCategoria = () => {
  if (form.categoria_id === 'nova') {
    mostraModalNovaCategoria.value = true;
    form.categoria_id = ''; // Limpa o valor para não ficar "nova" no select
  }
};

// Cancela a criação de nova categoria
const cancelarNovaCategoria = () => {
  mostraModalNovaCategoria.value = false;
  novaCategoria.value.nome = '';
};

// Salva uma nova categoria
const salvarNovaCategoria = async () => {
  try {
    const response = await axios.post(route('categorias.store'), {
      nome: novaCategoria.value.nome,
      cor: novaCategoria.value.cor
    });
    
    // Adiciona a nova categoria à lista
    const novaCategoriaCriada = response.data;
    categorias.value.push(novaCategoriaCriada);
    
    // Seleciona a categoria recém-criada
    form.categoria_id = novaCategoriaCriada.id;
    
    // Fecha o modal e limpa o formulário
    mostraModalNovaCategoria.value = false;
    novaCategoria.value.nome = '';
  } catch (error) {
    console.error('Erro ao criar categoria:', error);
    if (error.response && error.response.data && error.response.data.errors) {
      // Aqui poderia mostrar os erros de validação
      console.error('Erros de validação:', error.response.data.errors);
    }
  }
};

// Submeter o formulário principal
const submeter = () => {
  form.post(route('tarefas.store'));
};
</script> 