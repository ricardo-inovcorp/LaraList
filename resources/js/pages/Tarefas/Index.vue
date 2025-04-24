<template>
  <Head title="Tarefas" />

  <AppLayout>
    <div class="py-10">
      <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
            <span class="bg-gradient-to-r from-purple-500 to-indigo-600 bg-clip-text text-transparent">
              LaraList Pro
            </span>
          </h2>
          <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Gerencie suas tarefas e organize seu trabalho.</p>
        </div>
        <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
          <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 sm:mb-0 tracking-tight">
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
              class="flex items-center justify-center px-4 py-2 text-sm font-medium text-gray-700 dark:text-indigo-400 border border-gray-300 dark:border-indigo-500/30 bg-white dark:bg-indigo-600/20 backdrop-blur-sm rounded-md shadow-lg hover:bg-gray-50 dark:hover:bg-indigo-600/30 transition-all duration-200"
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
               'bg-green-50 dark:bg-green-500/10 border-green-200 dark:border-green-500/30 text-green-800 dark:text-green-400': $page.props.flash.tipo === 'sucesso',
               'bg-red-50 dark:bg-red-500/10 border-red-200 dark:border-red-500/30 text-red-800 dark:text-red-400': $page.props.flash.tipo === 'erro',
               'bg-blue-50 dark:bg-blue-500/10 border-blue-200 dark:border-blue-500/30 text-blue-800 dark:text-blue-400': $page.props.flash.tipo === 'info'
             }"
        >
          {{ $page.props.flash.mensagem }}
        </div>

        <!-- Filtros -->
        <div v-show="showFilters" class="mb-6 backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg overflow-hidden transition-all duration-300">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Filtros</h2>
          </div>
          <div class="p-6">
            <form @submit.prevent="aplicarFiltros">
              <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                  <InputLabel for="filtro_estado" value="Estado" class="text-gray-900 dark:text-white/50" />
                  <SelectInput
                    id="filtro_estado"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
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
                  <InputLabel for="filtro_prioridade" value="Prioridade" class="text-gray-900 dark:text-white/50" />
                  <SelectInput
                    id="filtro_prioridade"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
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
                  <InputLabel for="filtro_categoria" value="Categoria" class="text-gray-900 dark:text-white/50" />
                  <SelectInput
                    id="filtro_categoria"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white"
                    v-model="filtros.categoria_id"
                  >
                    <option value="">Todas</option>
                    <option 
                      v-for="categoria in categorias" 
                      :key="categoria.id" 
                      :value="categoria.id"
                      :style="{ color: categoria.cor }"
                    >
                      {{ categoria.nome }}
                    </option>
                  </SelectInput>
                </div>
                
                <div>
                  <InputLabel for="filtro_busca" value="Busca" class="text-gray-900 dark:text-white/50" />
                  <TextInput
                    id="filtro_busca"
                    type="text"
                    class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/20 focus:border-indigo-500 focus:ring-indigo-500 rounded-md text-gray-900 dark:text-white placeholder-gray-500 dark:placeholder-gray-400"
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
        <div class="backdrop-blur-lg bg-white/90 dark:bg-white/5 rounded-lg border border-gray-200 dark:border-white/10 shadow-lg overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
              <h2 class="text-xl font-bold text-gray-900 dark:text-white tracking-tight">Lista de Tarefas</h2>
              
              <div class="mt-2 md:mt-0 flex items-center text-xs text-gray-600 dark:text-gray-400">
                <span class="mr-4">Prioridades:</span>
                <div class="flex space-x-3">
                  <div class="flex items-center">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-gray-100 dark:bg-gray-900/30 text-gray-800 dark:text-gray-400 border border-gray-300 dark:border-gray-500/30 mr-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                      </svg>
                    </span>
                    <span>Baixa</span>
                  </div>
                  <div class="flex items-center">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400 border border-blue-300 dark:border-blue-500/30 mr-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 10a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span>Média</span>
                  </div>
                  <div class="flex items-center">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-orange-100 dark:bg-orange-900/30 text-orange-800 dark:text-orange-400 border border-orange-300 dark:border-orange-500/30 mr-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span>Alta</span>
                  </div>
                  <div class="flex items-center">
                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-400 border border-red-300 dark:border-red-500/30 mr-1">
                      <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                    </span>
                    <span>Urgente</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-white/10">
              <thead class="bg-gray-50 dark:bg-white/5">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    Título
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">
                    Estado
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden md:table-cell">
                    Prioridade
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden lg:table-cell">
                    Categoria
                  </th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider hidden lg:table-cell">
                    Data Conclusão
                  </th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-600 dark:text-gray-300 uppercase tracking-wider">
                    Ações
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-white/10 bg-white dark:bg-transparent">
                <tr v-for="tarefa in tarefas.data" :key="tarefa.id" 
                    class="transition-all duration-200 hover:bg-gray-50 dark:hover:bg-white/5 cursor-pointer"
                    @click="navegarParaDetalhe(tarefa.id)"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-medium text-gray-900 dark:text-white">
                          {{ tarefa.titulo }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                          {{ truncarTexto(tarefa.descricao, 20) }}
                        </div>
                        <!-- Informações visíveis apenas em mobile -->
                        <div class="mt-2 md:hidden space-y-2">
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium group relative mr-2" 
                                :class="estadoBadgeClasses(tarefa.estado)">
                            <svg v-if="tarefa.estado === 'pendente'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else-if="tarefa.estado === 'em_progresso'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else-if="tarefa.estado === 'concluida'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else-if="tarefa.estado === 'cancelada'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                            </svg>
                            {{ estadoLabel(tarefa.estado) }}
                            <span class="absolute -top-10 left-1/2 -translate-x-1/2 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-10">
                              Estado: {{ estadoLabel(tarefa.estado) }}
                            </span>
                          </span>
                          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium group relative" 
                                :class="prioridadeBadgeClasses(tarefa.prioridade)">
                            <svg v-if="tarefa.prioridade === 'baixa'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                              <path d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                            </svg>
                            <svg v-else-if="tarefa.prioridade === 'media'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M6 10a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else-if="tarefa.prioridade === 'alta'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" />
                            </svg>
                            <svg v-else-if="tarefa.prioridade === 'urgente'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                            </svg>
                            <span class="absolute -top-10 left-1/2 -translate-x-1/2 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-10">
                              Prioridade: {{ prioridadeLabel(tarefa.prioridade) }}
                            </span>
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium group relative" 
                          :class="estadoBadgeClasses(tarefa.estado)">
                      <svg v-if="tarefa.estado === 'pendente'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                      </svg>
                      <svg v-else-if="tarefa.estado === 'em_progresso'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd" />
                      </svg>
                      <svg v-else-if="tarefa.estado === 'concluida'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                      </svg>
                      <svg v-else-if="tarefa.estado === 'cancelada'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                      </svg>
                      {{ estadoLabel(tarefa.estado) }}
                      <span class="absolute -top-10 left-1/2 -translate-x-1/2 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-10">
                        Estado: {{ estadoLabel(tarefa.estado) }}
                      </span>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium group relative" 
                          :class="prioridadeBadgeClasses(tarefa.prioridade)">
                      <svg v-if="tarefa.prioridade === 'baixa'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" />
                      </svg>
                      <svg v-else-if="tarefa.prioridade === 'media'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M6 10a1 1 0 011-1h6a1 1 0 110 2H7a1 1 0 01-1-1z" clip-rule="evenodd" />
                      </svg>
                      <svg v-else-if="tarefa.prioridade === 'alta'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd" />
                      </svg>
                      <svg v-else-if="tarefa.prioridade === 'urgente'" xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                      </svg>
                      <span class="absolute -top-10 left-1/2 -translate-x-1/2 p-2 bg-gray-900 text-white text-xs rounded shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300 whitespace-nowrap z-10">
                        Prioridade: {{ prioridadeLabel(tarefa.prioridade) }}
                      </span>
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <span 
                      v-if="tarefa.categoria" 
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 border border-gray-200 dark:bg-gray-800/50 dark:text-gray-300 dark:border-gray-700"
                    >
                      {{ tarefa.categoria.nome }}
                    </span>
                    <span v-else class="text-gray-500 dark:text-gray-400">-</span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-300 hidden lg:table-cell">
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
      <div class="p-6 bg-white dark:bg-gray-900 rounded-lg backdrop-blur-lg bg-opacity-80 border border-gray-200 dark:border-white/10">
        <h2 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Confirmação</h2>
        <p class="text-gray-700 dark:text-gray-300 mb-6">
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
  confirmModal.value.show = false;
  
  router.delete(route('tarefas.destroy', confirmModal.value.tarefa.id));
};
</script> 