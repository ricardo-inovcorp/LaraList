# Testes do LaraList

Esta pasta contém os testes automatizados para o sistema LaraList. Os testes foram organizados em duas categorias principais:

## Testes Unitários (Unit Tests)

Localizados na pasta `/tests/Unit`, estes testes focam em validar componentes individuais do sistema de forma isolada:

1. **Testes de Modelos (/tests/Unit/Models)**
   - TarefaTest.php: Testa as relações, estados e comportamentos do modelo Tarefa

2. **Testes de Serviços (/tests/Unit/Services)**
   - TarefaLogServiceTest.php: Testa o serviço responsável por registrar e gerenciar logs de atividades das tarefas

3. **Testes de Notificações (/tests/Unit/Notifications)**
   - TarefaAtividadeNotificationTest.php: Testa o sistema de notificações por email para atividades relacionadas às tarefas

## Testes de Feature/Integração

Localizados na pasta `/tests/Feature`, estes testes validam o comportamento integrado de múltiplos componentes:

1. **Testes de Controladores (/tests/Feature/Controllers)**
   - TarefaControllerTest.php: Testa as rotas e endpoints relacionados à gestão de tarefas (criação, edição, exclusão, etc.)

2. **Testes de Fluxos Completos (/tests/Feature/Workflows)**
   - GerenciamentoTarefasWorkflowTest.php: Testa um ciclo de vida completo de uma tarefa, simulando o fluxo do usuário

## Execução dos Testes

Para executar todos os testes do sistema:

```bash
php artisan test
```

Para executar apenas os testes unitários:

```bash
php artisan test --testsuite=Unit
```

Para executar apenas os testes de feature/integração:

```bash
php artisan test --testsuite=Feature
```

Para executar um arquivo de teste específico:

```bash
php artisan test tests/Unit/Models/TarefaTest.php
```

## Cobertura de Código

Para obter relatórios de cobertura de código, é necessário ter o Xdebug ou PCOV instalado. Após a instalação, execute:

```bash
php artisan test --coverage
```

## Boas Práticas

Ao adicionar novos recursos ao sistema, considere:

1. Criar testes unitários para validar o comportamento isolado dos componentes
2. Criar testes de feature para validar a integração entre componentes
3. Seguir a convenção de nomenclatura para os métodos de teste: `test_descricao_do_que_esta_testando()`
4. Utilizar factories para criar dados de teste
5. Isolar o banco de dados de teste usando a trait `RefreshDatabase`

## Estrutura de Factories

O sistema inclui factories para facilitar a criação de dados de teste:

- **TarefaFactory**: Cria instâncias de tarefas com diferentes estados
- **TarefaLogFactory**: Cria logs de atividades para tarefas
- **CategoriaFactory**: Cria categorias para organizar as tarefas 